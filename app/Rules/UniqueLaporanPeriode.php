<?php

namespace App\Rules;

use App\Models\LaporanImut;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueLaporanPeriode implements ValidationRule
{
    protected ?int $ignoreId;
    protected ?int $reportYear;
    protected ?int $reportMonth;

    public function __construct(?int $ignoreId = null, ?int $reportYear = null, ?int $reportMonth = null)
    {
        $this->ignoreId = $ignoreId;
        $this->reportYear = $reportYear;
        $this->reportMonth = $reportMonth;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the values for both month and year
        $month = $attribute === 'report_month' ? $value : $this->reportMonth;
        $year = $attribute === 'report_year' ? $value : $this->reportYear;

        if (!$month || !$year) {
            return; // Can't validate if we don't have both values
        }

        // Check for existing reports with same period
        $existingQuery = LaporanImut::where('report_month', $month)
            ->where('report_year', $year);

        if ($this->ignoreId) {
            $existingQuery->where('id', '!=', $this->ignoreId);
        }

        $existingReport = $existingQuery->first();

        if ($existingReport) {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $monthName = $monthNames[$month] ?? $month;
            $fail("Laporan untuk periode {$monthName} {$year} sudah ada dengan nama: \"{$existingReport->name}\"");
        }
    }
}
