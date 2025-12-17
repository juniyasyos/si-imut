<?php

namespace App\Models;

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
        'compliance_weight',
        'is_critical_field',
        'conditional_logic',
        'order_index',
    ];

    protected $casts = [
        'validation_config' => 'array',
        'is_critical_field' => 'boolean',
        'conditional_logic' => 'array',
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

        switch ($this->field_type) {
            case 'boolean':
                return $this->scoreBooleanField($value);

            case 'single_select':
            case 'multi_select':
                return $this->scoreSelectField($value);

            case 'time_duration':
                return $this->scoreTimeDuration($value);

            case 'time_range':
                return $this->scoreTimeRange($value);

            case 'rating_scale':
                return $this->scoreRatingScale($value);

            default:
                return $this->scoreDefaultField($value);
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
        $totalScore = 0;
        $optionCount = 0;

        foreach ($values as $selectedValue) {
            $option = $this->options()->where('option_value', $selectedValue)->first();
            if ($option) {
                $totalScore += $option->compliance_value * 50; // 0=0, 1=50, 2=100
                $optionCount++;
            }
        }

        return $optionCount > 0 ? $totalScore / $optionCount : 0;
    }

    private function scoreTimeDuration($value): float
    {
        $config = $this->validation_config;
        $minMinutes = $config['duration_limits']['min_minutes'] ?? 0;
        $maxMinutes = $config['duration_limits']['max_minutes'] ?? PHP_INT_MAX;

        $duration = (int) $value;

        if ($duration >= $minMinutes && $duration <= $maxMinutes) {
            return 100;
        }

        return 0;
    }

    private function scoreTimeRange($value): float
    {
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
        // For text/number fields, just check if not empty
        return !empty($value) ? 100 : 0;
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
}
