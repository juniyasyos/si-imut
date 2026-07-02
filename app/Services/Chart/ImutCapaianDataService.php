<?php

namespace App\Services\Chart;

use App\Dto\CapaianResult;
use App\Services\Core\ImutCalculatorService;
use Illuminate\Support\Collection;

/**
 * Service untuk menghitung data capaian IMUT per kategori per bulan.
 * Menghasilkan data chart dan statistik ringkasan sekaligus (CapaianResult).
 *
 * Diekstrak dari ImutCapaianWidget::getOptions() untuk mengurangi
 * kompleksitas widget dan meningkatkan testability.
 */
class ImutCapaianDataService
{
    public function __construct(
        private readonly ImutCalculatorService $calculator,
    ) {}

    /**
     * Hitung capaian per kategori per bulan beserta statistik ringkasan.
     *
     * @param  Collection  $laporans  Laporan yang sudah difilter per periode
     * @param  string[]  $categories  Daftar short_name kategori yang dipilih
     * @param  int[]  $monthsInPeriod  Bulan-bulan dalam periode
     * @param  string  $periodLabel  Label periode (e.g. "Semester 1 2026")
     */
    public function calculatePeriodCapaian(
        Collection $laporans,
        array $categories,
        array $monthsInPeriod,
        string $periodLabel,
    ): CapaianResult {
        $chartData = $this->initializeChartData($categories, $monthsInPeriod);
        $stats = $this->initializeStats($categories, $periodLabel);

        $laporansByMonth = $this->groupLaporansByMonth($laporans, $monthsInPeriod);

        foreach ($laporansByMonth as $month => $monthlyLaporans) {
            $monthResult = $this->processMonthlyLaporans($monthlyLaporans, $categories);

            foreach ($categories as $shortName) {
                $total = $monthResult[$shortName]['total'];
                $achieved = $monthResult[$shortName]['achieved'];

                $chartData[$shortName][$month] = $total > 0
                    ? round(($achieved / $total) * 100, 2)
                    : 0;

                $stats['categories_detail'][$shortName]['total_imut'] += $total;
                $stats['categories_detail'][$shortName]['imut_meeting_standard'] += $achieved;
                $stats['categories_detail'][$shortName]['imut_below_standard'] += ($total - $achieved);
            }
        }

        return new CapaianResult(
            chartData: $chartData,
            statistikData: $this->finalizeStats($stats),
        );
    }

    /**
     * Kelompokkan laporan berdasarkan bulan.
     *
     * @return array<int, array>
     */
    private function groupLaporansByMonth(Collection $laporans, array $monthsInPeriod): array
    {
        $grouped = array_fill_keys($monthsInPeriod, []);

        foreach ($laporans as $laporan) {
            $month = (int) ($laporan->assessment_period_start
                ? $laporan->assessment_period_start->month
                : $laporan->report_month);

            if (isset($grouped[$month])) {
                $grouped[$month][] = $laporan;
            }
        }

        return $grouped;
    }

    /**
     * Proses semua laporan dalam satu bulan: hitung total dan achieved per kategori.
     *
     * @return array<string, array{total: int, achieved: int}>
     */
    private function processMonthlyLaporans(array $laporans, array $categories): array
    {
        $result = array_fill_keys($categories, ['total' => 0, 'achieved' => 0]);

        foreach ($laporans as $laporan) {
            foreach ($laporan->laporanUnitKerjas as $unitKerja) {
                foreach ($unitKerja->imutPenilaians as $penilaian) {
                    $profile = $penilaian->profile;
                    $category = $profile?->imutData?->categories;

                    if (! $category || ! $category->short_name) {
                        continue;
                    }

                    $shortName = $category->short_name;

                    if (! in_array($shortName, $categories, true)) {
                        continue;
                    }

                    $evaluation = $this->calculator->evaluatePenilaian(
                        $penilaian->numerator_value ?? 0,
                        $penilaian->denominator_value ?? 0,
                        $profile->target_value ?? 0,
                        $profile->target_operator ?? '>='
                    );

                    $result[$shortName]['total']++;
                    $result[$shortName]['achieved'] += $evaluation['is_achieved'] ? 1 : 0;
                }
            }
        }

        return $result;
    }

    /**
     * Inisialisasi struktur data chart (semua 0).
     *
     * @return array<string, array<int, float>>
     */
    private function initializeChartData(array $categories, array $monthsInPeriod): array
    {
        $data = [];

        foreach ($categories as $category) {
            $data[$category] = array_fill_keys($monthsInPeriod, 0);
        }

        return $data;
    }

    /**
     * Inisialisasi struktur statistik ringkasan.
     */
    private function initializeStats(array $categories, string $periodLabel): array
    {
        $stats = [
            'total_categories' => count($categories),
            'total_imut_indicators' => 0,
            'imut_meeting_standard' => 0,
            'imut_below_standard' => 0,
            'overall_achievement' => 0,
            'laporan_used' => "Data {$periodLabel}",
            'laporan_period' => $periodLabel,
            'categories_detail' => [],
        ];

        foreach ($categories as $category) {
            $stats['categories_detail'][$category] = [
                'category_name' => $category,
                'total_imut' => 0,
                'imut_meeting_standard' => 0,
                'imut_below_standard' => 0,
                'achievement_percentage' => 0,
            ];
        }

        return $stats;
    }

    /**
     * Finalisasi statistik: hitung total dan persentase keseluruhan.
     */
    private function finalizeStats(array $stats): array
    {
        foreach ($stats['categories_detail'] as $key => $catStat) {
            $total = $catStat['total_imut'];
            $achieved = $catStat['imut_meeting_standard'];

            $stats['categories_detail'][$key]['achievement_percentage'] = $total > 0
                ? round(($achieved / $total) * 100, 1)
                : 0;

            $stats['total_imut_indicators'] += $total;
            $stats['imut_meeting_standard'] += $achieved;
            $stats['imut_below_standard'] += $catStat['imut_below_standard'];
        }

        $stats['overall_achievement'] = $stats['total_imut_indicators'] > 0
            ? round(($stats['imut_meeting_standard'] / $stats['total_imut_indicators']) * 100, 1)
            : 0;

        $stats['categories_detail'] = array_values($stats['categories_detail']);

        return $stats;
    }
}
