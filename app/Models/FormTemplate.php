<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'imut_profile_id',
        'title',
        'description',
        'compliance_method',
        'auto_fail_on_critical',
        'scoring_config',
    ];

    protected $casts = [
        'auto_fail_on_critical' => 'boolean',
        'scoring_config' => 'array',
    ];

    public function imutProfile(): BelongsTo
    {
        return $this->belongsTo(ImutProfile::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(EnhancedFormField::class)->orderBy('order_index');
    }

    public function fields(): HasMany
    {
        return $this->formFields();
    }

    public function dailyReportResponses(): HasMany
    {
        return $this->hasMany(DailyReportResponse::class);
    }

    // Query Scopes
    public function scopeForUserUnits(Builder $query, User $user): Builder
    {
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        
        return $query->whereHas('imutProfile.imutData.unitKerja', function ($q) use ($unitKerjaIds) {
            $q->whereIn('unit_kerja.id', $unitKerjaIds);
        });
    }

    public function calculateCompliance(array $fieldResponses): array
    {
        $totalScore = 0;
        $maxScore = 0;
        $criticalFailed = false;

        foreach ($this->formFields as $field) {
            $response = $fieldResponses[$field->field_key] ?? null;
            $fieldScore = $field->calculateFieldScore($response);

            // Only include fields that contribute to scoring (have compliance weight > 0)
            // Text fields are excluded from compliance calculation
            if ($this->fieldContributesToScoring($field)) {
                $totalScore += $fieldScore * $field->compliance_weight;
                $maxScore += 100 * $field->compliance_weight;

                if ($field->is_critical_field && $fieldScore < 50) {
                    $criticalFailed = true;
                }
            }
        }

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        if ($this->auto_fail_on_critical && $criticalFailed) {
            $percentage = 0;
            $isCompliant = false;
        } else {
            $isCompliant = $percentage >= 80;
        }

        return [
            'total_score' => round($percentage, 2),
            'compliance_status' => $isCompliant,
            'critical_failed' => $criticalFailed,
            'calculation_details' => [
                'raw_score' => $totalScore,
                'max_score' => $maxScore,
                'weighted_percentage' => $percentage,
                'threshold_met' => $isCompliant,
            ]
        ];
    }

    /**
     * Determine if field contributes to compliance scoring
     * Only select, multi-select, and time fields have scoring values
     * Text fields are for supplementary data only
     */
    private function fieldContributesToScoring(EnhancedFormField $field): bool
    {
        return in_array($field->field_type, [
            'boolean',
            'single_select',
            'multi_select',
            'rating_scale',
            'time_duration',
            'time_range',
            'conditional_trigger',
            'compliance_checker'
        ]) && $field->compliance_weight > 0;
    }
}
