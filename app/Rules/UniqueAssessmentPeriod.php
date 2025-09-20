<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\LaporanImut;

/**
 * Validates that assessment period doesn't overlap with existing reports
 */
class UniqueAssessmentPeriod implements ValidationRule
{
    protected ?int $excludeId;

    public function __construct(?int $excludeId = null)
    {
        $this->excludeId = $excludeId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = request()->all();

        if (empty($data['assessment_period_start']) || empty($data['assessment_period_end'])) {
            return;
        }

        $startDate = $data['assessment_period_start'];
        $endDate = $data['assessment_period_end'];

        $query = LaporanImut::where(function ($q) use ($startDate, $endDate) {
            $q->where(function ($subQuery) use ($startDate, $endDate) {
                // New period starts during existing period
                $subQuery->where('assessment_period_start', '<=', $startDate)
                         ->where('assessment_period_end', '>=', $startDate);
            })->orWhere(function ($subQuery) use ($startDate, $endDate) {
                // New period ends during existing period
                $subQuery->where('assessment_period_start', '<=', $endDate)
                         ->where('assessment_period_end', '>=', $endDate);
            })->orWhere(function ($subQuery) use ($startDate, $endDate) {
                // New period encompasses existing period
                $subQuery->where('assessment_period_start', '>=', $startDate)
                         ->where('assessment_period_end', '<=', $endDate);
            });
        });

        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        if ($query->exists()) {
            $fail('Periode penilaian bertumpang tindih dengan laporan yang sudah ada.');
        }
    }
}
