<?php

namespace App\Modules\FormEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnhancedFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_template_id',
        'field_key',
        'field_label',
        'field_description',
        'field_type',
        'validation_config',
        'history_suggestions',
        'compliance_weight',
        'is_critical_field',
        'conditional_logic',
        'compliance_rules',
        'order_index',
        'time_format',
        'default_valid_duration',
    ];

    protected $casts = [
        'validation_config' => 'array',
        'history_suggestions' => 'array',
        'is_critical_field' => 'boolean',
        'conditional_logic' => 'array',
        'compliance_rules' => 'array',
        'default_valid_duration' => 'integer',
    ];

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class, 'enhanced_form_field_id')->orderBy('order_index');
    }

    public function fieldResponses(): HasMany
    {
        return $this->hasMany(FieldResponse::class, 'form_field_id');
    }

    public function calculateFieldScore($value): float
    {
        if ($value === null) {
            return 0;
        }

        // Only certain field types contribute to compliance scoring
        switch ($this->field_type) {
            case 'boolean':
                return $this->scoreBooleanField($value);

            case 'single_select':
            case 'multi_select':
            case 'conditional_trigger':
            case 'compliance_checker':
                return $this->scoreSelectField($value);

            case 'time_duration':
                return $this->scoreTimeDuration($value);

            case 'time_range':
                return $this->scoreTimeRange($value);

            case 'rating_scale':
                return $this->scoreRatingScale($value);

            case 'text':
            case 'date':
            case '': // field_type kosong dianggap text
                // Text fields: 100% jika diisi, 0% jika kosong
                return filled($value) ? 100 : 0;

            case 'short_text':
            case 'long_text':
            case 'number':
                // These field types are for supplementary data only
                // They don't contribute to compliance scoring
                return 0;

            default:
                return 0;
        }
    }

    private function scoreBooleanField($value): float
    {
        $config = $this->validation_config;
        $expectedValue = $config['expected_value'] ?? true;

        return ($value == $expectedValue) ? 100 : 0;
    }

    private function scoreSelectField($value): float
    {
        $values = is_array($value) ? $value : [$value];

        // For single select (not array), return 100 if is_correct = true, else 0
        if (!is_array($value)) {
            $option = $this->options()->where('option_value', $value)->first();
            if ($option) {
                return ($option->is_correct ?? false) ? 100 : 0;
            }
            return 0;
        }

        // For multi-select, apply boolean rules
        $correctSelected = 0;
        $wrongSelected = 0;

        foreach ($values as $selectedValue) {
            $option = $this->options()->where('option_value', $selectedValue)->first();
            if ($option) {
                if ($option->is_correct ?? false) {
                    $correctSelected++;
                } else {
                    $wrongSelected++;
                }
            }
        }

        // Apply compliance rules
        $complianceRules = $this->compliance_rules ?? [];
        $minCorrect = $complianceRules['minimum_correct'] ?? 1;
        $allowWrong = $complianceRules['allow_wrong_selections'] ?? true;

        // Simple boolean: Pass or Fail
        $passesMinimum = $correctSelected >= $minCorrect;
        $passesWrongRule = $allowWrong || $wrongSelected == 0;

        return ($passesMinimum && $passesWrongRule) ? 100 : 0;
    }

    private function scoreTimeDuration($value): float
    {
        // $value sekarang adalah array dengan keys: start_time, end_time, valid_indicator, valid_duration_setting
        if (!is_array($value) || !isset($value['start_time']) || !isset($value['end_time'])) {
            return 0;
        }

        $startTime = $value['start_time'];
        $endTime = $value['end_time'];
        $thresholdTime = $value['valid_duration_setting'] ?? '08:00';
        $threshold = $this->convertTimeToMinutes($thresholdTime);

        if (!$startTime || !$endTime) {
            return 0;
        }

        try {
            // Try HH:mm:ss first, then fallback to HH:mm
            try {
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
            } catch (\Exception $e) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
            }

            try {
                $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
            } catch (\Exception $e) {
                $end = \Carbon\Carbon::createFromFormat('H:i', $endTime);
            }

            // Handle case where end time is next day
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $durationInMinutes = $start->diffInMinutes($end);

            // Score 100 jika durasi <= threshold, 0 jika tidak
            return ($durationInMinutes <= $threshold) ? 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function scoreTimeRange($value): float
    {
        // Handle composite array from time_range field
        if (is_array($value)) {
            $inputValue = $value['input_value'] ?? null;
            $startTime = $value['start_time'] ?? null;
            $endTime = $value['end_time'] ?? null;

            if (!$inputValue || !$startTime || !$endTime) {
                return 0;
            }

            // Check if input_value is within start_time and end_time range
            if ($inputValue >= $startTime && $inputValue <= $endTime) {
                return 100;
            }

            return 0;
        }

        // Fallback for legacy string value
        $config = $this->validation_config;
        $minTime = $config['time_range']['min_time'] ?? '00:00';
        $maxTime = $config['time_range']['max_time'] ?? '23:59';

        $timeValue = date('H:i', strtotime($value));

        if ($timeValue >= $minTime && $timeValue <= $maxTime) {
            return 100;
        }

        return 0;
    }

    private function scoreRatingScale($value): float
    {
        $config = $this->validation_config;
        $minRating = $config['min_rating'] ?? 1;
        $maxRating = $config['max_rating'] ?? 5;
        $passingScore = $config['passing_score'] ?? 4;

        $rating = (int) $value;

        if ($rating >= $passingScore) {
            return 100;
        } elseif ($rating >= $minRating) {
            return ($rating / $passingScore) * 100;
        }

        return 0;
    }

    private function scoreDefaultField($value): float
    {
        // This method is deprecated - scoring is now handled per field type
        // Text fields should not contribute to compliance scoring
        return 0;
    }

    public function shouldShowField(array $allResponses): bool
    {
        if (!$this->conditional_logic) {
            return true;
        }

        $logic = $this->conditional_logic;
        $parentField = $logic['parent_field'] ?? null;

        if (!$parentField || !isset($allResponses[$parentField])) {
            return true;
        }

        $parentValue = $allResponses[$parentField];
        $conditions = $logic['trigger_conditions'] ?? [];

        foreach ($conditions as $condition) {
            if ($condition['when_value'] == $parentValue) {
                return $condition['action'] !== 'hide_all_below' &&
                    $condition['action'] !== 'hide_fields';
            }
        }

        return true;
    }

    private function convertTimeToMinutes(string $time): int
    {
        try {
            $carbon = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            return ($carbon->hour * 60) + $carbon->minute;
        } catch (\Exception $e) {
            return 480; // fallback 8 hours
        }
    }
}
