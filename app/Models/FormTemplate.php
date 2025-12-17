<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'imut_data_id',
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

    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class);
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

    public function calculateCompliance(array $fieldResponses): array
    {
        $totalScore = 0;
        $maxScore = 0;
        $criticalFailed = false;

        foreach ($this->formFields as $field) {
            $response = $fieldResponses[$field->field_key] ?? null;
            $fieldScore = $field->calculateFieldScore($response);

            $totalScore += $fieldScore * $field->compliance_weight;
            $maxScore += 100 * $field->compliance_weight;

            if ($field->is_critical_field && $fieldScore < 50) {
                $criticalFailed = true;
            }
        }

        $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

        if ($this->auto_fail_on_critical && $criticalFailed) {
            $percentage = 0;
            $status = 'non_compliant';
        } else {
            $status = $percentage >= 80 ? 'compliant' : 'non_compliant';
        }

        return [
            'total_score' => round($percentage, 2),
            'compliance_status' => $status,
            'critical_failed' => $criticalFailed,
            'calculation_details' => [
                'raw_score' => $totalScore,
                'max_score' => $maxScore,
                'weighted_percentage' => $percentage,
            ]
        ];
    }
}
