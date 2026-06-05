<?php

namespace App\Services\DailyReport;

use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\EnhancedFormField;
use App\Models\ImutPenilaian;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;

/**
 * Service untuk membangun FieldResponse records dengan konsisten
 * Menormalkan storage untuk semua field types
 */
class FieldResponseBuilderService
{
    private UnifiedComplianceService $complianceService;

    public function __construct(UnifiedComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Build dan create FieldResponse untuk field tertentu
     * 
     * Overloaded method - accepts pre-calculated compliance score
     * Used for single-pass scoring optimization in DailyReportBuildService
     */
    public function buildWithScore(
        DailyReportResponse $dailyReport,
        EnhancedFormField $field,
        array $formData,
        float $preCalculatedScore
    ): FieldResponse {
        $fieldValue = $formData[$field->field_key] ?? null;
        $normalizedValue = $this->normalizeFieldValue($field, $fieldValue, $formData);

        $imutPenilaian = $this->findImutPenilaian($dailyReport);

        return FieldResponse::create([
            'daily_report_response_id' => $dailyReport->id,
            'form_field_id' => $field->id,
            'imut_penilaian_id' => $imutPenilaian?->id,
            'field_value' => $normalizedValue,
            'compliance_score' => $preCalculatedScore,
        ]);
    }

    /**
     * Build dan create FieldResponse untuk field tertentu (Legacy - calculates score)
     * 
     * This method is deprecated for new code - use buildWithScore() for single-pass scoring
     */
    public function build(
        DailyReportResponse $dailyReport,
        EnhancedFormField $field,
        array $formData
    ): FieldResponse {
        $fieldValue = $formData[$field->field_key] ?? null;
        $complianceScore = $this->complianceService->scoreField($field, $fieldValue);

        $normalizedValue = $this->normalizeFieldValue($field, $fieldValue, $formData);

        $imutPenilaian = $this->findImutPenilaian($dailyReport);

        return FieldResponse::create([
            'daily_report_response_id' => $dailyReport->id,
            'form_field_id' => $field->id,
            'imut_penilaian_id' => $imutPenilaian?->id,
            'field_value' => $normalizedValue,
            'compliance_score' => $complianceScore,
        ]);
    }

    /**
     * Find ImutPenilaian for a given DailyReportResponse
     * Traces: FormTemplate → ImutProfil → ImutPenilaian (matching unit & period)
     */
    private function findImutPenilaian(DailyReportResponse $dailyReport): ?ImutPenilaian
    {
        // Get FormTemplate to find ImutProfile relationship
        $formTemplate = $dailyReport->formTemplate;
        if (!$formTemplate || !$formTemplate->imut_profile_id) {
            return null;
        }

        // Find ImutPenilaian matching:
        // - imut_profil_id from FormTemplate
        // - unit_kerja_id from LaporanUnitKerja matches DailyReportResponse
        // - report_date within assessment period of LaporanImut
        return ImutPenilaian::query()
            ->where('imut_profil_id', $formTemplate->imut_profile_id)
            ->whereHas('laporanUnitKerja', function ($q) use ($dailyReport) {
                $q->where('unit_kerja_id', $dailyReport->unit_kerja_id);
            })
            ->whereHas('laporanUnitKerja.laporanImut', function ($q) use ($dailyReport) {
                $q->where('assessment_period_start', '<=', $dailyReport->report_date)
                  ->where('assessment_period_end', '>=', $dailyReport->report_date);
            })
            ->first();
    }

    /**
     * Normalize field value ke format storage yang konsisten
     */
    private function normalizeFieldValue(
        EnhancedFormField $field,
        $fieldValue,
        array $formData
    ): mixed {
        return match ($field->field_type) {
            'time_duration' => $this->normalizeTimeDuration($field, $fieldValue, $formData),
            'time_range' => $this->normalizeTimeRange($field, $fieldValue, $formData),
            'checkbox' => $this->normalizeCheckbox($fieldValue),
            default => $this->normalizeGeneric($fieldValue),
        };
    }

    /**
     * Normalize time duration field - composite structure
     */
    private function normalizeTimeDuration(
        EnhancedFormField $field,
        $fieldValue,
        array $formData
    ): array {
        $startTime = $formData[$field->field_key . '_start_time'] ?? null;
        $endTime = $formData[$field->field_key . '_end_time'] ?? null;
        $validDuration = $formData[$field->field_key . '_valid_duration_setting'] ?? null;
        $thresholdType = $field->validation_config['threshold_type'] ?? 'less_than';

        $isValid = TimeUtility::checkDurationValidity(
            $startTime,
            $endTime,
            $validDuration,
            $thresholdType
        ) ? '1' : '0';

        return [
            'type' => 'time_duration',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'valid_duration_setting' => $validDuration,
            'valid_indicator' => $isValid,
        ];
    }

    /**
     * Normalize time range field
     */
    private function normalizeTimeRange(
        EnhancedFormField $field,
        $fieldValue,
        array $formData
    ): array {
        $inputValue = $formData[$field->field_key . '_input_value'] ?? null;
        $startTime = $formData[$field->field_key . '_start_time'] ?? null;
        $endTime = $formData[$field->field_key . '_end_time'] ?? null;

        $isValid = ($inputValue && $startTime && $endTime) ? '1' : '0';

        return [
            'type' => 'time_range',
            'input_value' => $inputValue,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'valid_indicator' => $isValid,
        ];
    }

    /**
     * Normalize checkbox - ensure array format
     */
    private function normalizeCheckbox($fieldValue): array
    {
        if (!is_array($fieldValue)) {
            return [$fieldValue];
        }

        return $fieldValue;
    }

    /**
     * Normalize generic field - simple value storage
     */
    private function normalizeGeneric($fieldValue): mixed
    {
        if (is_array($fieldValue)) {
            return $fieldValue;
        }

        return $fieldValue ?? [];
    }
}
