<?php

namespace App\Modules\DailyReport\Services;

use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
use App\Modules\FormEngine\Models\EnhancedFormField;
use App\Modules\FormEngine\Models\FormTemplate;
use Illuminate\Support\Collection;

/**
 * Service terpusat untuk perhitungan compliance.
 *
 * Menjadi single source of truth untuk scoring logic
 * pada daily report / form response.
 */
class UnifiedComplianceService
{
    /*
    |--------------------------------------------------------------------------
    | Public API
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate compliance untuk form berdasarkan responses yang diberikan.
     *
     * Semua field scoring memiliki bobot yang sama, yaitu 1.
     *
     * @param  FormTemplate  $template
     * @param  array  $responses
     * @return array{
     *     score: float,
     *     status: string,
     *     compliance_status: bool,
     *     critical_failed: bool,
     *     calculation_details: array,
     *     breakdown: array,
     *     fields: array
     * }
     */
    public function calculate(FormTemplate $template, array $responses): array
    {
        $this->loadTemplateScoringRelations($template);

        $totalScore = 0.0;
        $maxScore = 0.0;
        $criticalFailed = false;
        $fieldScores = [];

        foreach ($template->formFields as $field) {
            $response = $this->resolveFieldResponse($field, $responses);
            $fieldScore = $this->scoreField($field, $response);

            $fieldScores[$field->field_key] = $fieldScore;

            if (!$this->fieldContributesToScoring($field)) {
                continue;
            }

            $totalScore += $fieldScore;
            $maxScore += 100;

            if ($this->isCriticalFieldFailed($field, $fieldScore)) {
                $criticalFailed = true;
            }
        }


        $percentage = $maxScore > 0
            ? ($totalScore / $maxScore) * 100
            : 0.0;

        // if (($template->auto_fail_on_critical ?? false) && $criticalFailed) {
        //     $percentage = 0.0;
        // }

        $isCompliant = !(($template->auto_fail_on_critical ?? false) && $criticalFailed)
            && round($percentage, 2) >= 100;

        $fieldBreakdown = $this->getFieldBreakdown($template->formFields, $fieldScores);

        // if ($fieldScores["hand_hygiene_method"] > 0) {
        //     }
        // dd([
        //     'totalScore' => $totalScore,
        //     'maxScore' => $maxScore,
        //     'criticalFailed' => $criticalFailed,
        //     'fieldScores' => $fieldScores,
        //     'percentage' => $percentage,
        //     'isCompliant' => $isCompliant,
        //     'fieldBreakdown' => $fieldBreakdown,
        // ]);

        return [
            'score' => round($percentage, 2),
            'status' => $this->getComplianceStatus($percentage),
            'compliance_status' => $isCompliant,
            'critical_failed' => $criticalFailed,

            'calculation_details' => [
                'raw_score' => round($totalScore, 2),
                'max_score' => round($maxScore, 2),
                'weighted_percentage' => round($percentage, 2),
                'threshold_met' => $isCompliant,
                'field_breakdown' => $fieldBreakdown,
            ],

            'breakdown' => [
                'field_breakdown' => $fieldBreakdown,
                'raw_score' => round($totalScore, 2),
                'max_score' => round($maxScore, 2),
            ],

            'fields' => $fieldBreakdown,
        ];
    }

    /**
     * Score individual field berdasarkan field type.
     */
    public function scoreField(EnhancedFormField $field, mixed $fieldValue): float
    {
        if ($this->isEmptyResponse($fieldValue)) {
            return $this->scoreEmptyField($field);
        }


        return match ($field->field_type) {
            'time_duration' => $this->scoreTimeDuration($field, (array) $fieldValue),
            'time_range' => $this->scoreTimeRange($field, (array) $fieldValue),

            'select',
            'single_select',
            'radio' => $this->scoreSingleOptionField($field, $fieldValue),

            'multi_select' => $this->scoreMultiOptionField($field, $fieldValue),

            'boolean' => $this->scoreBooleanField($fieldValue),
            'rating_scale' => $this->scoreRatingScaleField($field, $fieldValue),

            'text',
            'textarea',
            'number',
            'email',
            'url' => 100.0,

            default => $this->scoreGenericField($fieldValue),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Option-Based Field Scoring
    |--------------------------------------------------------------------------
    */

    /**
     * Score select, single_select, dan radio.
     */
    private function scoreSingleOptionField(EnhancedFormField $field, mixed $fieldValue): float
    {
        $selectedValue = $this->normalizeSingleValue($fieldValue);

        if (!filled($selectedValue)) {
            return 0.0;
        }

        $options = $this->getScoringOptions($field);

        if ($options->isEmpty()) {
            return 100.0;
        }

        $selectedOption = $options->firstWhere('value', $selectedValue);

        if (!$selectedOption) {
            return 0.0;
        }

        if ($this->hasCorrectOptions($options) && !$selectedOption['is_correct']) {
            return 0.0;
        }

        return $this->normalizeScore($selectedOption['compliance_value']);
    }

    /**
     * Score multi_select / checkbox group.
     */
    private function scoreMultiOptionField(EnhancedFormField $field, mixed $fieldValue): float
    {
        $selectedValues = $this->normalizeMultiValues($fieldValue);

        if ($selectedValues->isEmpty()) {
            return 0.0;
        }

        $options = $this->getScoringOptions($field);

        if ($options->isEmpty()) {
            return 0.0;
        }

        $rules = $this->getComplianceRules($field);

        $selectedOptions = $options->whereIn('value', $selectedValues);

        if ($selectedOptions->isEmpty()) {
            return 0.0;
        }

        $correctOptions = $options->where('is_correct', true);
        $selectedCorrectOptions = $selectedOptions->where('is_correct', true);
        $selectedWrongOptions = $selectedOptions->where('is_correct', false);

        if ($this->hasInvalidWrongSelection($rules, $selectedWrongOptions)) {
            return 0.0;
        }

        $correctSelectedCount = $selectedCorrectOptions->count();
        $totalCorrectOptions = $correctOptions->count();

        if ($totalCorrectOptions <= 0) {
            return 0.0;
        }

        if ($correctSelectedCount < $rules['minimum_correct']) {
            return $this->normalizeScore(
                ($correctSelectedCount / $rules['minimum_correct']) * 100
            );
        }

        if ($correctSelectedCount >= $totalCorrectOptions) {
            return 100.0;
        }

        return $this->normalizeScore(
            ($correctSelectedCount / $rules['minimum_correct']) * 100
        );
    }

    private function hasInvalidWrongSelection(array $rules, Collection $selectedWrongOptions): bool
    {
        return !$rules['allow_wrong_selections']
            && $selectedWrongOptions->isNotEmpty();
    }

    /*
    |--------------------------------------------------------------------------
    | Time-Based Field Scoring
    |--------------------------------------------------------------------------
    */

    /**
     * Score time duration field dengan validasi threshold.
     */
    private function scoreTimeDuration(EnhancedFormField $field, array $fieldValue): float
    {
        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;

        if (!$startTime || !$endTime) {
            return 0.0;
        }

        $validDuration = $fieldValue['valid_duration_setting'] ?? null;
        $thresholdType = $field->validation_config['threshold_type'] ?? 'less_than';

        $isValid = TimeUtility::checkDurationValidity(
            $startTime,
            $endTime,
            $validDuration,
            $thresholdType
        );

        return $isValid ? 100.0 : 50.0;
    }

    /**
     * Score time range field.
     */
    private function scoreTimeRange(EnhancedFormField $field, array $fieldValue): float
    {
        $inputValue = $fieldValue['input_value'] ?? null;
        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;
        $validIndicator = $fieldValue['valid_indicator'] ?? null;

        if (!filled($inputValue)) {
            return 0.0;
        }

        if ((string) $validIndicator === '1') {
            return 100.0;
        }

        if (!$startTime || !$endTime) {
            return 50.0;
        }

        return 0.0;
    }

    /*
    |--------------------------------------------------------------------------
    | Basic Field Scoring
    |--------------------------------------------------------------------------
    */

    /**
     * Score field kosong.
     *
     * Field informatif seperti text tetap dianggap 100
     * karena tidak menjadi checklist compliance benar/salah.
     */
    private function scoreEmptyField(EnhancedFormField $field): float
    {
        return in_array($field->field_type, [
            'text',
            'textarea',
            'number',
            'email',
            'url',
        ], true)
            ? 100.0
            : 0.0;
    }

    /**
     * Score boolean field.
     */
    private function scoreBooleanField(mixed $fieldValue): float
    {
        return filter_var($fieldValue, FILTER_VALIDATE_BOOLEAN)
            ? 100.0
            : 0.0;
    }

    /**
     * Score rating scale field.
     */
    private function scoreRatingScaleField(EnhancedFormField $field, mixed $fieldValue): float
    {
        if (!is_numeric($fieldValue)) {
            return 0.0;
        }

        $rules = $field->compliance_rules ?? [];

        $value = (float) $fieldValue;
        $maxValue = max((float) ($rules['max_score'] ?? 5), 1);

        return $this->normalizeScore(($value / $maxValue) * 100);
    }

    /**
     * Score generic / unknown field type.
     */
    private function scoreGenericField(mixed $fieldValue): float
    {
        return $this->isEmptyResponse($fieldValue) ? 0.0 : 100.0;
    }

    /*
    |--------------------------------------------------------------------------
    | Option Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil option yang bisa digunakan untuk scoring.
     */
    private function getScoringOptions(EnhancedFormField $field): Collection
    {
        $options = $field->relationLoaded('options')
            ? $field->options
            : $field->options()->get();

        return $options
            ->map(fn($option) => [
                'value' => $option->option_value,
                'is_correct' => (bool) $option->is_correct,
                'compliance_value' => (float) ($option->compliance_value ?? 100),
            ])
            ->filter(fn(array $option) => filled($option['value']))
            ->values();
    }

    /**
     * Ambil compliance rules dengan default aman.
     */
    private function getComplianceRules(EnhancedFormField $field): array
    {
        $rules = $field->compliance_rules ?? [];

        return [
            'minimum_correct' => max((int) ($rules['minimum_correct'] ?? 1), 1),
            'allow_wrong_selections' => (bool) ($rules['allow_wrong_selections'] ?? true),
        ];
    }

    /**
     * Cek apakah options punya opsi benar.
     */
    private function hasCorrectOptions(Collection $options): bool
    {
        return $options->contains(
            fn(array $option) => $option['is_correct'] === true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Value Normalizers
    |--------------------------------------------------------------------------
    */

    /**
     * Normalisasi value untuk single option field.
     */
    private function normalizeSingleValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return collect($value)
                ->filter(fn($item) => filled($item))
                ->first();
        }

        return $value;
    }

    /**
     * Normalisasi value untuk multi option field.
     */
    private function normalizeMultiValues(mixed $value): Collection
    {
        return collect(is_array($value) ? $value : [$value])
            ->filter(fn($item) => filled($item))
            ->unique()
            ->values();
    }

    /**
     * Cek response kosong secara aman.
     */
    private function isEmptyResponse(mixed $value): bool
    {
        if (is_array($value)) {
            return collect($value)
                ->filter(fn($item) => filled($item))
                ->isEmpty();
        }

        return blank($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Score Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Pastikan skor selalu berada di range 0 - 100.
     */
    private function normalizeScore(float|int $score): float
    {
        return round(min(max((float) $score, 0.0), 100.0), 2);
    }

    /**
     * Cek apakah field critical gagal.
     */
    private function isCriticalFieldFailed(EnhancedFormField $field, float $score): bool
    {
        return (bool) ($field->is_critical_field ?? false) && $score < 50;
    }

    /*
    |--------------------------------------------------------------------------
    | Breakdown & Status Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Determine compliance status berdasarkan score.
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
     * Build field breakdown untuk detailed reporting.
     */
    private function getFieldBreakdown(iterable $formFields, array $fieldScores): array
    {
        $breakdown = [];

        foreach ($formFields as $field) {
            $breakdown[] = [
                'field_id' => $field->id,
                'field_key' => $field->field_key,
                'field_label' => $field->field_label ?? null,
                'field_type' => $field->field_type,
                'score' => $fieldScores[$field->field_key] ?? 0,
                'weight' => 1,
                'is_critical' => (bool) ($field->is_critical_field ?? false),
            ];
        }

        return $breakdown;
    }

    /*
    |--------------------------------------------------------------------------
    | Template Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Load relasi yang dibutuhkan untuk scoring.
     */
    private function loadTemplateScoringRelations(FormTemplate $template): void
    {
        if (!$template->relationLoaded('formFields')) {
            $template->load('formFields.options');

            return;
        }

        $template->formFields->loadMissing('options');
    }

    /**
     * Determine if a field contributes to scoring.
     *
     * Tidak lagi memakai compliance_weight.
     * Semua field yang tipenya masuk daftar ini dianggap punya bobot 1.
     */
    private function fieldContributesToScoring(EnhancedFormField $field): bool
    {
        return in_array($field->field_type, [
            'text',
            'textarea',
            'number',
            'email',
            'url',
            'boolean',
            'select',
            'single_select',
            'radio',
            'multi_select',
            'rating_scale',
            'time_duration',
            'time_range',
            'conditional_trigger',
            'compliance_checker',
        ], true);
    }

    /*
|--------------------------------------------------------------------------
| Response Resolvers
|--------------------------------------------------------------------------
*/

    private function resolveFieldResponse(EnhancedFormField $field, array $responses): mixed
    {
        return match ($field->field_type) {
            'time_range' => $this->resolveTimeRangeResponse($field, $responses),
            'time_duration' => $this->resolveTimeDurationResponse($field, $responses),

            default => $responses[$field->field_key] ?? null,
        };
    }

    private function resolveTimeRangeResponse(EnhancedFormField $field, array $responses): array
    {
        return [
            'input_value' => $responses[$field->field_key . '_input_value'] ?? null,
            'start_time' => $responses[$field->field_key . '_start_time'] ?? null,
            'end_time' => $responses[$field->field_key . '_end_time'] ?? null,
            'valid_indicator' => $responses[$field->field_key . '_valid_indicator'] ?? null,
        ];
    }

    private function resolveTimeDurationResponse(EnhancedFormField $field, array $responses): array
    {
        return [
            'start_time' => $responses[$field->field_key . '_start_time'] ?? null,
            'end_time' => $responses[$field->field_key . '_end_time'] ?? null,
            'valid_duration_setting' => $responses[$field->field_key . '_valid_duration_setting'] ?? null,
            'valid_indicator' => $responses[$field->field_key . '_valid_indicator'] ?? null,
        ];
    }
}