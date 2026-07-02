<?php

namespace App\Services\Chart;

use Illuminate\Support\Collection;

/**
 * Service untuk mengelola logik periode (triwulan, semester, tahun).
 * Diekstrak dari ImutCapaianWidget untuk reusability.
 */
class ImutPeriodService
{
    /**
     * Mapping nama bulan dalam Bahasa Indonesia.
     */
    public const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Mapping bulan per triwulan.
     */
    private const QUARTER_MONTHS = [
        1 => [1, 2, 3],
        2 => [4, 5, 6],
        3 => [7, 8, 9],
        4 => [10, 11, 12],
    ];

    /**
     * Generate opsi dropdown periode berdasarkan laporan yang tersedia.
     *
     * @return array<string, string> [key => label]
     */
    public function getAvailablePeriods(Collection $laporans, string $periodType): array
    {
        $periods = [];

        foreach ($laporans as $laporan) {
            $year = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->year
                : $laporan->report_year;

            $month = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->month
                : $laporan->report_month;

            if (! $year || ! $month) {
                continue;
            }

            [$key, $label] = $this->buildPeriodKeyLabel($periodType, (int) $year, (int) $month);

            if ($key && $label) {
                $periods[$key] = $label;
            }
        }

        krsort($periods);

        return $periods;
    }

    /**
     * Parse selected period string menjadi metadata (year, months, label).
     *
     * @return array{year: int, months: int[], label: string}
     */
    public function parsePeriod(string $periodType, string $selectedPeriod): array
    {
        return match ($periodType) {
            'quarter' => $this->parseQuarter($selectedPeriod),
            'semester' => $this->parseSemester($selectedPeriod),
            default => $this->parseYear($selectedPeriod),
        };
    }

    /**
     * Konversi array bulan menjadi array nama bulan.
     *
     * @param  int[]  $months
     * @return string[]
     */
    public function getMonthLabels(array $months): array
    {
        return array_map(
            fn (int $month) => self::MONTH_NAMES[$month] ?? '',
            $months
        );
    }

    /**
     * Filter collection laporan berdasarkan tahun dan bulan.
     */
    public function filterByPeriod(Collection $laporans, int $year, array $months): Collection
    {
        return $laporans->filter(function ($laporan) use ($year, $months) {
            $lYear = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->year
                : $laporan->report_year;

            $lMonth = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->month
                : $laporan->report_month;

            return $lYear == $year && in_array((int) $lMonth, $months, true);
        });
    }

    /**
     * Build key dan label untuk satu entry periode.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function buildPeriodKeyLabel(string $periodType, int $year, int $month): array
    {
        return match ($periodType) {
            'quarter' => [
                "{$year}-Q".((int) ceil($month / 3)),
                'Triwulan '.((int) ceil($month / 3))." {$year}",
            ],
            'semester' => [
                "{$year}-S".($month <= 6 ? 1 : 2),
                'Semester '.($month <= 6 ? 1 : 2)." {$year}",
            ],
            'year' => [(string) $year, "Tahun {$year}"],
            default => [null, null],
        };
    }

    /**
     * @return array{year: int, months: int[], label: string}
     */
    private function parseQuarter(string $selectedPeriod): array
    {
        [$year, $quarterRaw] = explode('-Q', $selectedPeriod);

        $quarter = (int) $quarterRaw;

        return [
            'year' => (int) $year,
            'months' => self::QUARTER_MONTHS[$quarter] ?? [1, 2, 3],
            'label' => "Triwulan {$quarter} {$year}",
        ];
    }

    /**
     * @return array{year: int, months: int[], label: string}
     */
    private function parseSemester(string $selectedPeriod): array
    {
        [$year, $semesterRaw] = explode('-S', $selectedPeriod);

        $semester = (int) $semesterRaw;

        return [
            'year' => (int) $year,
            'months' => $semester === 1 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12],
            'label' => "Semester {$semester} {$year}",
        ];
    }

    /**
     * @return array{year: int, months: int[], label: string}
     */
    private function parseYear(string $selectedPeriod): array
    {
        $year = (int) $selectedPeriod;

        return [
            'year' => $year,
            'months' => range(1, 12),
            'label' => "Tahun {$year}",
        ];
    }
}
