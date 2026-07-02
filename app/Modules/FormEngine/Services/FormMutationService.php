<?php

namespace App\Modules\FormEngine\Services;

use App\Models\DailyReportResponse;
use App\Modules\FormEngine\Models\FieldResponse;
use App\Modules\FormEngine\Models\FormTemplate;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Service untuk menangani form mutations dan data creation
 * Memisahkan business logic dari Filament Pages
 */
class FormMutationService
{
    /**
     * Mutate dan prepare form data untuk pembuatan record
     *
     * @param array $data
     * @param FormTemplate $formTemplate
     * @param string|null $reportDate
     * @return array
     * @throws \Exception
     */
    public function prepareDailyReportData(
        array $data,
        FormTemplate $formTemplate,
        ?string $reportDate = null
    ): array {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Anda harus login terlebih dahulu');
        }

        $unitKerjaId = $user->unitKerjas()->first()?->id;

        if (!$unitKerjaId) {
            throw new \Exception('Anda tidak terdaftar di unit kerja mana pun');
        }

        $date = $reportDate ? Carbon::createFromFormat('Y-m-d', $reportDate) : now();

        return [
            'form_template_id' => $formTemplate->id,
            'unit_kerja_id' => $unitKerjaId,
            'submitted_by' => Auth::id(),
            'report_date' => $date,
            'data' => $data,
        ];
    }

    /**
     * Create DailyReportResponse dan FieldResponse records
     *
     * @param array $preparedData
     * @param FormTemplate $formTemplate
     * @return DailyReportResponse
     */
    public function createDailyReport(
        array $preparedData,
        FormTemplate $formTemplate
    ): DailyReportResponse {
        $repo = app(\App\Repositories\Interfaces\DailyReportResponseRepositoryInterface::class);

        // Create DailyReportResponse via repository
        $dailyReport = $repo->createReport([
            'form_template_id' => $preparedData['form_template_id'],
            'unit_kerja_id' => $preparedData['unit_kerja_id'],
            'submitted_by' => $preparedData['submitted_by'],
            'report_date' => $preparedData['report_date'],
            'total_score' => 0,
            'compliance_status' => 'pending',
            'auto_calculated' => true,
        ]);

        // Create field responses
        $this->createFieldResponses($dailyReport, $formTemplate, $preparedData['data']);

        return $dailyReport;
    }

    /**
     * Create FieldResponse records untuk setiap form field
     */
    private function createFieldResponses(
        DailyReportResponse $dailyReport,
        FormTemplate $formTemplate,
        array $responseData
    ): void {
        $sortedFields = $formTemplate->formFields->sortBy('order_index');
        $totalScore = 0;
        $fieldCount = 0;

        foreach ($sortedFields as $field) {
            $fieldValue = $responseData[$field->field_key] ?? null;

            if ($field->field_type === 'time_duration') {
                $this->createTimeDurationFieldResponse(
                    $dailyReport,
                    $field,
                    $responseData
                );
            } else {
                $complianceScore = ($fieldValue !== null && $fieldValue !== '') ? 100 : 0;

                FieldResponse::create([
                    'daily_report_response_id' => $dailyReport->id,
                    'form_field_id' => $field->id,
                    'field_value' => $fieldValue,
                    'compliance_score' => $complianceScore,
                ]);

                $totalScore += $complianceScore;
                $fieldCount++;
            }
        }

        // Update total score
        if ($fieldCount > 0) {
            $dailyReport->update([
                'total_score' => round($totalScore / $fieldCount),
            ]);
        }
    }

    /**
     * Create FieldResponse untuk time_duration field type
     */
    private function createTimeDurationFieldResponse(
        DailyReportResponse $dailyReport,
        $field,
        array $responseData
    ): void {
        $startTime = $responseData[$field->field_key . '_start_time'] ?? null;
        $endTime = $responseData[$field->field_key . '_end_time'] ?? null;
        $validDuration = $responseData[$field->field_key . '_valid_duration_setting'] ?? null;

        $thresholdType = $field->validation_config['threshold_type'] ?? 'less_than';
        $validIndicator = TimeUtility::checkDurationValidity(
            $startTime,
            $endTime,
            $validDuration,
            $thresholdType
        ) ? '1' : '0';

        $complianceScore = ($startTime && $endTime) ? (($validIndicator == '1') ? 100 : 0) : 0;

        FieldResponse::create([
            'daily_report_response_id' => $dailyReport->id,
            'form_field_id' => $field->id,
            'field_value' => [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'valid_duration_setting' => $validDuration,
                'valid_indicator' => $validIndicator,
            ],
            'compliance_score' => $complianceScore,
        ]);
    }
}
