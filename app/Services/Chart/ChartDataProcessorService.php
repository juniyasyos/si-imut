<?php

namespace App\Services\Chart;

use Carbon\Carbon;
use App\Services\Core\ImutCalculatorService;
use Illuminate\Support\Collection;

/**
 * Service untuk memproses data chart IMUT
 * Focus: Transform data collections ke format chart
 */
class ChartDataProcessorService
{
    public function __construct(
        private ImutCalculatorService $calculator
    ) {}

    /**
     * Process laporan data untuk chart capaian
     *
     * @param Collection $laporans
     * @param array $categories
     * @return array
     */
    public function processCapaianData(Collection $laporans, array $categories): array
    {
        $data = $this->initializeCategoryData($categories, $laporans->count());

        foreach ($laporans as $index => $laporan) {
            $laporanResults = $this->processLaporanForCapaian($laporan);

            foreach ($categories as $shortName) {
                $categoryData = $laporanResults[$shortName] ?? ['total' => 0, 'achieved' => 0];

                $data[$shortName][$index] = $categoryData['total'] > 0
                    ? round(($categoryData['achieved'] / $categoryData['total']) * 100, 2)
                    : 0;
            }
        }

        return $data;
    }

    /**
     * Process single laporan untuk capaian per kategori
     *
     * @param mixed $laporan
     * @param array $categories
     * @return array
     */
    public function processCategoryAchievementData($laporan, array $categories): array
    {
        $results = array_fill_keys($categories, 0);
        $categoryData = [];

        foreach ($laporan->laporanUnitKerjas as $unitKerja) {
            foreach ($unitKerja->imutPenilaians as $penilaian) {
                $profile = $penilaian->profile;
                $category = $profile?->imutData?->categories;

                if (!$category || !$category->short_name) {
                    continue;
                }

                $shortName = $category->short_name;
                if (!in_array($shortName, $categories, true)) {
                    continue;
                }

                $evaluation = $this->calculator->evaluatePenilaian(
                    $penilaian->numerator_value ?? 0,
                    $penilaian->denominator_value ?? 0,
                    $profile->target_value ?? 0,
                    $profile->target_operator ?? '>='
                );

                $categoryData[$shortName]['total'] = ($categoryData[$shortName]['total'] ?? 0) + 1;
                $categoryData[$shortName]['achieved'] = ($categoryData[$shortName]['achieved'] ?? 0) + ($evaluation['is_achieved'] ? 1 : 0);
            }
        }

        foreach ($categories as $shortName) {
            if (isset($categoryData[$shortName]) && $categoryData[$shortName]['total'] > 0) {
                $results[$shortName] = round(($categoryData[$shortName]['achieved'] / $categoryData[$shortName]['total']) * 100, 2);
            }
        }

        return array_values($results);
    }

    /**
     * Process single laporan untuk capaian calculation
     *
     * @param mixed $laporan
     * @return array
     */
    private function processLaporanForCapaian($laporan): array
    {
        $results = [];

        foreach ($laporan->laporanUnitKerjas as $unitKerja) {
            foreach ($unitKerja->imutPenilaians as $penilaian) {
                $profile = $penilaian->profile;
                $category = $profile?->imutData?->categories;

                if (!$category || !$category->short_name) {
                    continue;
                }

                $evaluation = $this->calculator->evaluatePenilaian(
                    $penilaian->numerator_value ?? 0,
                    $penilaian->denominator_value ?? 0,
                    $profile->target_value ?? 0,
                    $profile->target_operator ?? '>='
                );

                $shortName = $category->short_name;
                $results[$shortName]['total'] = ($results[$shortName]['total'] ?? 0) + 1;
                $results[$shortName]['achieved'] = ($results[$shortName]['achieved'] ?? 0) + ($evaluation['is_achieved'] ? 1 : 0);
            }
        }

        return $results;
    }

    /**
     * Initialize category data array
     *
     * @param array $categories
     * @param int $count
     * @return array
     */
    private function initializeCategoryData(array $categories, int $count): array
    {
        $data = [];
        foreach ($categories as $shortName) {
            $data[$shortName] = array_fill(0, $count, 0);
        }
        return $data;
    }

    /**
     * Build chart series dari processed data
     *
     * @param array $processedData
     * @param array $formData
     * @param array $colors
     * @return array
     */
    public function buildChartSeries(array $processedData, array $formData, array $colors): array
    {
        $series = [];
        $colorIndex = 0;

        foreach ($processedData as $shortName => $data) {
            $series[] = [
                'name' => $shortName,
                'type' => $formData['series_types'][$shortName] ?? 'column',
                'data' => $data,
                'color' => $formData['series_colors'][$shortName] ?? $colors[$colorIndex % count($colors)]
            ];
            $colorIndex++;
        }

        return $series;
    }

    /**
     * Generate time-based labels dari laporan collection
     *
     * @param Collection $laporans
     * @return array
     */
    public function generateTimeLabels(Collection $laporans): array
    {
        return $laporans->map(function ($laporan) {
            $start = $laporan->assessment_period_start
                ? Carbon::parse($laporan->assessment_period_start)
                : null;
            $end = $laporan->assessment_period_end
                ? Carbon::parse($laporan->assessment_period_end)
                : null;

            if (!$start || !$end) {
                return 'Tidak diketahui';
            }

            return $start->month === $end->month
                ? $start->day . ' - ' . $end->day . ' ' . $start->translatedFormat('F Y')
                : $start->translatedFormat('j F') . ' - ' . $end->translatedFormat('j F Y');
        })->toArray();
    }

    /**
     * Process data untuk unit kerja specific chart
     *
     * @param Collection $penilaianData
     * @param array $config
     * @return array
     */
    public function processUnitKerjaChartData(Collection $penilaianData, array $config): array
    {
        $dataNilai = [];
        $dataTarget = [];
        $labels = [];

        $monthNames = [
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
            12 => 'Desember'
        ];

        foreach ($penilaianData as $row) {
            $label = $monthNames[$row->report_month] . ' ' . $row->report_year;
            $percentage = $this->calculator->calculatePercentage(
                $row->total_num ?? 0,
                $row->total_denum ?? 0
            );
            $target = round($row->target ?? 0, 2);

            $labelKey = Carbon::parse($row->periode . '-01')->format('Y-m');
            $labels[$labelKey] = $label;
            $dataNilai[$labelKey] = $percentage;
            $dataTarget[$labelKey] = $target;
        }

        return [
            'labels' => array_keys($dataNilai),
            'nilai' => $dataNilai,
            'target' => $dataTarget
        ];
    }

    /**
     * Build series untuk unit kerja chart
     *
     * @param array $chartData dari processUnitKerjaChartData
     * @param array $config
     * @return array
     */
    public function buildUnitKerjaChartSeries(array $chartData, array $config): array
    {
        $labels = $chartData['labels'];

        return [
            [
                'name' => 'Nilai IMUT',
                'type' => $config['nilai_type'] ?? 'column',
                'data' => array_map(fn($l) => $chartData['nilai'][$l] ?? 0, $labels),
                'color' => $config['color_nilai'] ?? '#3b82f6',
            ],
            [
                'name' => 'Target Standar',
                'type' => $config['target_type'] ?? 'line',
                'data' => array_map(fn($l) => $chartData['target'][$l] ?? 0, $labels),
                'color' => $config['color_target'] ?? '#f59e0b',
            ],
        ];
    }
}
