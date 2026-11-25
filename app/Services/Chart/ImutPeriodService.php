<?php

namespace App\Services\Chart;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service untuk mengelola logik periode (bulan, triwulan/kuartal, semester, tahun).
 * Digunakan bersama oleh ImutCapaianWidget, ImutTercapai, ImutTidakTercapai, dan PrintReportController.
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
     * Mapping label tipe periode.
     */
    public const PERIOD_TYPE_LABELS = [
        'month' => 'Bulan',
        'quarter' => 'Kuartal',
        'semester' => 'Semester',
        'year' => 'Tahun',
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
                ? Carbon::parse($laporan->assessment_period_start)->year
                : $laporan->report_year;

            $month = $laporan->assessment_period_start
                ? Carbon::parse($laporan->assessment_period_start)->month
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
            'month' => $this->parseMonth($selectedPeriod),
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
                ? Carbon::parse($laporan->assessment_period_start)->year
                : $laporan->report_year;

            $lMonth = $laporan->assessment_period_start
                ? Carbon::parse($laporan->assessment_period_start)->month
                : $laporan->report_month;

            return (int) $lYear === $year && in_array((int) $lMonth, $months, true);
        });
    }

    /**
     * Resolve tipe periode dan nilai periode terpilih menjadi konteks lengkap laporan.
     *
     * @return array{
     *     periodType: string,
     *     periodTypeLabel: string,
     *     options: array<string, string>,
     *     selectedPeriod: ?string,
     *     label: string,
     *     laporans: Collection,
     *     laporanIds: int[],
     *     primaryLaporan: mixed,
     *     startDate: ?Carbon,
     *     endDate: ?Carbon,
     *     status: string,
     *     reportName: string
     * }
     */
    public function resolvePeriodContext(
        Collection $laporans,
        string $periodType = 'month',
        ?string $selectedPeriod = null
    ): array {
        if (! array_key_exists($periodType, self::PERIOD_TYPE_LABELS)) {
            $periodType = 'month';
        }

        $options = $this->getAvailablePeriods($laporans, $periodType);

        if (empty($options)) {
            return [
                'periodType' => $periodType,
                'periodTypeLabel' => self::PERIOD_TYPE_LABELS[$periodType],
                'options' => [],
                'selectedPeriod' => null,
                'label' => '-',
                'laporans' => collect(),
                'laporanIds' => [],
                'primaryLaporan' => null,
                'startDate' => null,
                'endDate' => null,
                'status' => 'process',
                'reportName' => '-',
            ];
        }

        if (! $selectedPeriod || ! array_key_exists($selectedPeriod, $options)) {
            $selectedPeriod = (string) array_key_first($options);
        }

        $meta = $this->parsePeriod($periodType, $selectedPeriod);
        $matched = $this->filterByPeriod($laporans, $meta['year'], $meta['months'])
            ->sortBy('assessment_period_start')
            ->values();

        $laporanIds = $matched->pluck('id')->map(fn ($id) => (int) $id)->all();
        $primaryLaporan = $matched->last();

        $startDates = $matched->pluck('assessment_period_start')->filter()->map(fn ($d) => Carbon::parse($d));
        $endDates = $matched->pluck('assessment_period_end')->filter()->map(fn ($d) => Carbon::parse($d));

        $startDate = $startDates->isNotEmpty() ? $startDates->min() : null;
        $endDate = $endDates->isNotEmpty() ? $endDates->max() : null;

        $allComplete = $matched->isNotEmpty() && $matched->every(fn ($item) => ($item->status ?? '') === 'complete');
        $status = $periodType === 'month'
            ? ($primaryLaporan?->status ?? 'process')
            : ($allComplete ? 'complete' : 'process');

        $reportName = $periodType === 'month'
            ? ($primaryLaporan?->name ?? $meta['label'])
            : "Rekap {$meta['label']} ({$matched->count()} Laporan Bulanan)";

        return [
            'periodType' => $periodType,
            'periodTypeLabel' => self::PERIOD_TYPE_LABELS[$periodType],
            'options' => $options,
            'selectedPeriod' => $selectedPeriod,
            'label' => $meta['label'],
            'laporans' => $matched,
            'laporanIds' => $laporanIds,
            'primaryLaporan' => $primaryLaporan,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'reportName' => $reportName,
        ];
    }

    /**
     * Build key dan label untuk satu entry periode.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function buildPeriodKeyLabel(string $periodType, int $year, int $month): array
    {
        return match ($periodType) {
            'month' => [
                sprintf('%04d-%02d', $year, $month),
                (self::MONTH_NAMES[$month] ?? $month)." {$year}",
            ],
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
    private function parseMonth(string $selectedPeriod): array
    {
        $parts = explode('-', $selectedPeriod);
        $year = (int) ($parts[0] ?? now()->year);
        $month = max(1, min(12, (int) ($parts[1] ?? now()->month)));
        $monthName = self::MONTH_NAMES[$month] ?? (string) $month;

        return [
            'year' => $year,
            'months' => [$month],
            'label' => "{$monthName} {$year}",
        ];
    }

    /**
     * @return array{year: int, months: int[], label: string}
     */
    private function parseQuarter(string $selectedPeriod): array
    {
        $parts = explode('-Q', $selectedPeriod);
        $year = (int) ($parts[0] ?? now()->year);
        $quarter = max(1, min(4, (int) ($parts[1] ?? 1)));

        return [
            'year' => $year,
            'months' => self::QUARTER_MONTHS[$quarter] ?? [1, 2, 3],
            'label' => "Triwulan {$quarter} {$year}",
        ];
    }

    /**
     * @return array{year: int, months: int[], label: string}
     */
    private function parseSemester(string $selectedPeriod): array
    {
        $parts = explode('-S', $selectedPeriod);
        $year = (int) ($parts[0] ?? now()->year);
        $semester = (int) ($parts[1] ?? 1) === 2 ? 2 : 1;

        return [
            'year' => $year,
            'months' => $semester === 1 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12],
            'label' => "Semester {$semester} {$year}",
        ];
    }

    /**
     * @return array{year: int, months: int[], label: string}
     */
    private function parseYear(string $selectedPeriod): array
    {
        $year = (int) $selectedPeriod ?: now()->year;

        return [
            'year' => $year,
            'months' => range(1, 12),
            'label' => "Tahun {$year}",
        ];
    }
}
