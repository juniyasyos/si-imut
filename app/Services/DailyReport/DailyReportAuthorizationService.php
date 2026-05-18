<?php

namespace App\Services\DailyReport;

use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\FormTemplate;
use App\Models\User;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders\TimeRangeFieldBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service for Daily Report Entry creation
 * Handles all business logic for creating daily reports, field responses, and compliance calculations
 */
class DailyReportAuthorizationService
{
    /**
     * Authorize user access to create report for indicator
     */
    public function authorizeUserAccess(User $user, int $indicatorId): bool
    {
        if (!$user) {
            return false;
        }

        // Fetch template with relations for unit check
        $template = FormTemplate::with('imutProfile.imutData.unitKerja')->find($indicatorId);

        if (!$template || !$template->imutProfile) {
            return false;
        }

        // Global view permission bypasses unit restrictions
        if ($user->can('view_all_data_imut::data')) {
            return true;
        }

        // Check unit kerja access
        if (!$user->can('view_by_unit_kerja_imut::data')) {
            return false;
        }

        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        $hasUnitAccess = $template->imutProfile->imutData
            ->unitKerja()
            ->whereIn('unit_kerja_id', $userUnitIds)
            ->exists();

        return $hasUnitAccess;
    }

    /**
     * Resolve template valid for specific report date
     */
    public function resolveTemplateForDate(FormTemplate $requestedTemplate, string $reportDate): ?FormTemplate
    {
        try {
            $profile = $requestedTemplate->imutProfile;

            if (!$profile) {
                return null;
            }

            $templateForDate = $profile->formTemplates()
                ->whereDate('valid_from', '<=', $reportDate)
                ->where(function ($query) use ($reportDate) {
                    $query->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', $reportDate);
                })
                ->orderByDesc('is_active')
                ->orderByDesc('valid_from')
                ->first();

            return $templateForDate ?: $profile->activeFormTemplate;
        } catch (\Exception $e) {
            Log::error('Error resolving template for date', [
                'template_id' => $requestedTemplate->id,
                'date' => $reportDate,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create daily report response with field responses
     */
    public function createDailyReportWithResponses(User $user, FormTemplate $formTemplate, string $reportDate, array $formData): DailyReportResponse
    {
        try {
            // Validate user has ID
            if (!$user->id) {
                throw new \Exception('User ID tidak valid. Pastikan Anda sudah login dengan benar.');
            }

            // Get user's unit kerja
            $unitKerjaId = $user->unitKerjas()->first()?->id;

            if (!$unitKerjaId) {
                throw new \Exception('User tidak terdaftar di unit kerja mana pun');
            }

            // Parse report date
            $parsedDate = $this->parseReportDate($reportDate);

            // Prepare data for creation with explicit validation
            $reportData = [
                'form_template_id' => $formTemplate->id,
                'unit_kerja_id' => $unitKerjaId,
                'submitted_by' => (int)$user->id,  // Ensure it's an integer
                'report_date' => $parsedDate,
                'total_score' => 0,
                'compliance_status' => 'pending',
                'auto_calculated' => true,
            ];

            // Validate all required fields are set
            $requiredFields = ['form_template_id', 'unit_kerja_id', 'submitted_by', 'report_date'];
            foreach ($requiredFields as $field) {
                if (empty($reportData[$field]) && $reportData[$field] !== 0) {
                    throw new \Exception("Field '{$field}' tidak boleh kosong. Nilai: " . var_export($reportData[$field], true));
                }
            }

            Log::debug('Creating daily report with data:', $reportData);

            // Create daily report response
            $dailyReport = DailyReportResponse::create($reportData);

            if (!$dailyReport->id) {
                throw new \Exception('Gagal membuat record daily report. Silakan coba lagi.');
            }

            // Process and create field responses
            $responses = $this->createFieldResponses($dailyReport, $formTemplate, $formData);

            // Calculate compliance
            $complianceResult = $formTemplate->calculateCompliance($responses);

            // Update daily report with compliance results
            $dailyReport->update([
                'total_score' => $complianceResult['total_score'],
                'compliance_status' => $complianceResult['compliance_status'],
                'calculation_details' => $complianceResult,
            ]);

            Log::info('Daily report created successfully', [
                'id' => $dailyReport->id,
                'template_id' => $formTemplate->id,
                'user_id' => $user->id,
                'compliance_score' => $complianceResult['total_score']
            ]);

            return $dailyReport;
        } catch (\Exception $e) {
            Log::error('Error creating daily report', [
                'template_id' => $formTemplate->id,
                'user_id' => $user->id,
                'user_exists' => $user->exists,
                'date' => $reportDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create field responses for daily report
     */
    private function createFieldResponses(DailyReportResponse $dailyReport, FormTemplate $formTemplate, array $formData): array
    {
        $responses = [];
        $sortedFields = $formTemplate->formFields->sortBy('order_index');

        foreach ($sortedFields as $field) {
            $fieldValue = $formData[$field->field_key] ?? null;

            // Handle based on field type
            if ($field->field_type === 'time_duration') {
                $this->handleTimeDurationField($field, $formData, $dailyReport, $responses);
            } elseif ($field->field_type === 'time_range') {
                $this->handleTimeRangeField($field, $formData, $dailyReport, $responses);
            } else {
                $this->handleRegularField($field, $fieldValue, $dailyReport, $responses, $formData);
            }
        }

        return $responses;
    }

    /**
     * Handle time_duration field type
     */
    private function handleTimeDurationField(
        $field,
        array $formData,
        DailyReportResponse $dailyReport,
        array &$responses
    ): void {
        try {
            $startTime = $formData[$field->field_key . '_start_time'] ?? null;
            $endTime = $formData[$field->field_key . '_end_time'] ?? null;
            $validDuration = $formData[$field->field_key . '_valid_duration_setting'] ?? null;
            $thresholdType = $field->validation_config['threshold_type'] ?? 'less_than';

            $validIndicator = TimeUtility::checkDurationValidity(
                $startTime,
                $endTime,
                $validDuration,
                $thresholdType
            ) ? '1' : '0';

            // Store in responses for compliance calculation
            $responses[$field->field_key] = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'valid_duration_setting' => $validDuration,
                'valid_indicator' => $validIndicator,
            ];
            $responses[$field->field_key . '_start_time'] = $startTime;
            $responses[$field->field_key . '_end_time'] = $endTime;
            $responses[$field->field_key . '_valid_duration_setting'] = $validDuration;
            $responses[$field->field_key . '_valid_indicator'] = $validIndicator;

            // Create field response record
            FieldResponse::create([
                'daily_report_response_id' => $dailyReport->id,
                'form_field_id' => $field->id,
                'field_value' => [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'valid_duration_setting' => $validDuration,
                    'valid_indicator' => $validIndicator,
                ],
                'compliance_score' => ($startTime && $endTime) ? (($validIndicator == '1') ? 100 : 0) : 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling time_duration field', [
                'field_id' => $field->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle time_range field type
     */
    private function handleTimeRangeField(
        $field,
        array $formData,
        DailyReportResponse $dailyReport,
        array &$responses
    ): void {
        try {
            $inputValue = $formData[$field->field_key . '_input_value'] ?? null;
            $validationConfig = $field->validation_config ?? [];
            $startTime = $validationConfig['default_start_time'] ?? '00:00';
            $endTime = $validationConfig['default_end_time'] ?? '23:59';

            $validIndicator = TimeRangeFieldBuilder::isInputValueValid($inputValue, $startTime, $endTime) ? '1' : '0';

            // Store in responses for compliance calculation
            $responses[$field->field_key] = [
                'input_value' => $inputValue,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'valid_indicator' => $validIndicator,
            ];
            $responses[$field->field_key . '_input_value'] = $inputValue;
            $responses[$field->field_key . '_start_time'] = $startTime;
            $responses[$field->field_key . '_end_time'] = $endTime;
            $responses[$field->field_key . '_valid_indicator'] = $validIndicator;

            // Create field response record
            FieldResponse::create([
                'daily_report_response_id' => $dailyReport->id,
                'form_field_id' => $field->id,
                'field_value' => [
                    'input_value' => $inputValue,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'valid_indicator' => $validIndicator
                ],
                'compliance_score' => $inputValue ? (($validIndicator == '1') ? 100 : 0) : 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling time_range field', [
                'field_id' => $field->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle regular field types (text, select, boolean, etc.)
     */
    private function handleRegularField(
        $field,
        $fieldValue,
        DailyReportResponse $dailyReport,
        array &$responses,
        array $formData
    ): void {
        try {
            $responses[$field->field_key] = $fieldValue;

            // Create field response record
            FieldResponse::create([
                'daily_report_response_id' => $dailyReport->id,
                'form_field_id' => $field->id,
                'field_value' => $fieldValue !== null ? (is_array($fieldValue) ? $fieldValue : [$fieldValue]) : [],
                'compliance_score' => $fieldValue !== null ? ($field->calculateFieldScore($fieldValue) ?? 0) : 0,
            ]);

            // Update history suggestions for text fields
            if (in_array($field->field_type, ['text', '']) && is_string($fieldValue) && !empty(trim($fieldValue))) {
                $this->updateHistorySuggestions($field, $fieldValue);
            }
        } catch (\Exception $e) {
            Log::error('Error handling regular field', [
                'field_id' => $field->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update history suggestions for text fields
     */
    private function updateHistorySuggestions($field, string $newValue): void
    {
        try {
            $currentSuggestions = $field->history_suggestions ?? [];

            // Add new value to the beginning if not already present
            if (!in_array($newValue, $currentSuggestions)) {
                array_unshift($currentSuggestions, $newValue);
                $field->update(['history_suggestions' => $currentSuggestions]);
            }
        } catch (\Exception $e) {
            Log::warning('Error updating history suggestions', [
                'field_id' => $field->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - history suggestions are not critical
        }
    }

    /**
     * Parse report date string to Carbon instance
     */
    private function parseReportDate(string $reportDate): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $reportDate);
        } catch (\Exception $e) {
            Log::warning('Invalid report date format, using today', [
                'date' => $reportDate,
                'error' => $e->getMessage()
            ]);
            return now();
        }
    }

    /**
     * Validate template exists and is accessible
     */
    public function validateTemplateAccess(int $templateId, User $user): ?FormTemplate
    {
        try {
            $template = FormTemplate::with('imutProfile.imutData.unitKerja')->find($templateId);

            if (!$template || !$template->imutProfile) {
                return null;
            }

            // Check access permission
            if (!$this->authorizeUserAccess($user, $templateId)) {
                return null;
            }

            return $template;
        } catch (\Exception $e) {
            Log::error('Error validating template access', [
                'template_id' => $templateId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
