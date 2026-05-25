<?php

namespace App\Rules;

use Illuminate\Translation\PotentiallyTranslatedString;
use App\Models\ImutBenchmarking;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoBenchmarkingOverlap implements ValidationRule
{
    protected int $imutDataId;
    protected int $regionTypeId;
    protected ?int $ignoreBenchmarkingId;

    public function __construct(
        int $imutDataId,
        int $regionTypeId,
        ?int $ignoreBenchmarkingId = null
    ) {
        $this->imutDataId = $imutDataId;
        $this->regionTypeId = $regionTypeId;
        $this->ignoreBenchmarkingId = $ignoreBenchmarkingId;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string):PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Extract year, month, and period dates from the value
        // Assuming value is an array with keys: year, month, period_start, period_end
        if (!is_array($value)) {
            return;
        }

        $year = $value['year'] ?? null;
        $month = $value['month'] ?? null;
        $periodStart = $value['period_start'] ?? null;
        $periodEnd = $value['period_end'] ?? null;

        if (!$year || !$month || !$periodStart) {
            return; // Let other validation rules handle required fields
        }

        // Check for overlapping benchmarking periods
        $query = ImutBenchmarking::query()
            ->where('imut_data_id', $this->imutDataId)
            ->where('region_type_id', $this->regionTypeId)
            ->where('is_active', true);

        // Ignore current record when updating
        if ($this->ignoreBenchmarkingId) {
            $query->where('id', '!=', $this->ignoreBenchmarkingId);
        }

        // Check for period overlap
        $overlapping = $query->where(function ($q) use ($periodStart, $periodEnd) {
            $q->where(function ($q2) use ($periodStart, $periodEnd) {
                // New period starts within existing period
                $q2->where('period_start', '<=', $periodStart)
                    ->where(function ($q3) use ($periodStart) {
                        $q3->whereNull('period_end')
                            ->orWhere('period_end', '>=', $periodStart);
                    });
            })
            ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                // New period ends within existing period (if end date is set)
                if ($periodEnd) {
                    $q2->where('period_start', '<=', $periodEnd)
                        ->where(function ($q3) use ($periodEnd) {
                            $q3->whereNull('period_end')
                                ->orWhere('period_end', '>=', $periodEnd);
                        });
                }
            })
            ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                // New period completely encompasses existing period
                if ($periodEnd) {
                    $q2->where('period_start', '>=', $periodStart)
                        ->where(function ($q3) use ($periodEnd) {
                            $q3->where('period_end', '<=', $periodEnd)
                                ->orWhereNull('period_end');
                        });
                }
            });
        })->exists();

        if ($overlapping) {
            $fail("Periode benchmarking untuk tahun {$year} bulan {$month} bertumpang tindih dengan benchmarking yang sudah ada.");
        }
    }
}
