<?php

namespace App\Services\DailyReport;

use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;

/**
 * Service terpusat untuk compliance calculation
 * Single source of truth untuk scoring logic
 */
class UnifiedComplianceService
{
    /**
     * Calculate compliance untuk form dengan given responses
     *
     * @param FormTemplate $template
     * @param array $responses - Field responses dari form
     * @return array{score: float, status: string, breakdown: array, fields: array}
     */
    public function calculate(FormTemplate $template, array $responses): array
    {
        // Load formFields jika belum
        if (!$template->relationLoaded('formFields')) {
            $template->load('formFields.options');
        }

        $fieldScores = [];
        $totalScore = 0;
        $fieldCount = 0;

        foreach ($template->formFields as $field) {
            $fieldScore = $this->scoreField($field, $responses[$field->field_key] ?? null);
            $fieldScores[$field->field_key] = $fieldScore;

            $totalScore += $fieldScore;
            $fieldCount++;
        }

        // Calculate average compliance
        $averageScore = $fieldCount > 0 ? round($totalScore / $fieldCount) : 0;

        return [
            'score' => $averageScore,
            'status' => $this->getComplianceStatus($averageScore),
            'breakdown' => $fieldScores,
            'fields' => $this->getFieldBreakdown($template->formFields, $fieldScores),
        ];
    }

    /**
     * Score individual field berdasarkan tipenya
     */
    public function scoreField(EnhancedFormField $field, $fieldValue): float
    {
        // If no value provided, score is 0
        if ($fieldValue === null || $fieldValue === '') {
            return 0;
        }

        switch ($field->field_type) {
            case 'time_duration':
                return $this->scoreTimeDuration($field, $fieldValue);

            case 'time_range':
                return $this->scoreTimeRange($field, $fieldValue);

            case 'select':
            case 'radio':
                return $this->scoreSelection($field, $fieldValue);

            case 'checkbox':
                return $this->scoreCheckbox($field, $fieldValue);

            case 'text':
            case 'textarea':
            case 'number':
            case 'email':
            case 'url':
                // For simple fields, filled = 100%
                return 100;

            default:
                return $this->scoreGenericField($field, $fieldValue);
        }
    }

    /**
     * Score time duration field dengan validation
     */
    private function scoreTimeDuration(EnhancedFormField $field, array $fieldValue): float
    {
        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;

        // If times not provided, score 0
        if (!$startTime || !$endTime) {
            return 0;
        }

        // Check if duration meets validation threshold
        $validDuration = $fieldValue['valid_duration_setting'] ?? null;
        $thresholdType = $field->validation_config['threshold_type'] ?? 'less_than';

        $isValid = TimeUtility::checkDurationValidity(
            $startTime,
            $endTime,
            $validDuration,
            $thresholdType
        );

        return $isValid ? 100 : 50; // Partial credit if not meeting threshold
    }

    /**
     * Score time range field
     */
    private function scoreTimeRange(EnhancedFormField $field, array $fieldValue): float
    {
        $inputValue = $fieldValue['input_value'] ?? null;
        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;

        // Need at least input value
        if (!$inputValue) {
            return 0;
        }

        // If times also provided, full score
        if ($startTime && $endTime) {
            return 100;
        }

        // Only input value = partial credit
        return 50;
    }

    /**
     * Score selection field (select, radio)
     */
    private function scoreSelection(EnhancedFormField $field, $fieldValue): float
    {
        // If value selected, full score
        return (!empty($fieldValue)) ? 100 : 0;
    }

    /**
     * Score checkbox group
     */
    private function scoreCheckbox(EnhancedFormField $field, $fieldValue): float
    {
        if (!is_array($fieldValue)) {
            $fieldValue = [$fieldValue];
        }

        $selectedCount = count(array_filter($fieldValue));
        $totalOptions = $field->options()->count() ?? 1;

        // Proportional scoring based on selections
        return $totalOptions > 0 ? round(($selectedCount / $totalOptions) * 100) : 0;
    }

    /**
     * Score generic/unknown field types
     */
    private function scoreGenericField(EnhancedFormField $field, $fieldValue): float
    {
        // Simply check if field has value
        return (!empty($fieldValue)) ? 100 : 0;
    }

    /**
     * Determine compliance status berdasarkan score
     */
    private function getComplianceStatus(float $score): string
    {
        return match (true) {
            $score >= 90 => 'compliant',
            $score >= 70 => 'partial',
            $score > 0 => 'non_compliant',
            default => 'incomplete',
        };
    }

    /**
     * Build field breakdown untuk detailed reporting
     */
    private function getFieldBreakdown($formFields, array $fieldScores): array
    {
        $breakdown = [];

        foreach ($formFields as $field) {
            $breakdown[] = [
                'field_id' => $field->id,
                'field_key' => $field->field_key,
                'field_label' => $field->label,
                'field_type' => $field->field_type,
                'score' => $fieldScores[$field->field_key] ?? 0,
                'weight' => $field->compliance_weight ?? 1,
            ];
        }

        return $breakdown;
    }
}
