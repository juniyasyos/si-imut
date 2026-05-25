<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Support\CacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    /**
     * Get chart data for specific IMUT indicator
     */
    public function getImutChartData(Request $request, ImutData $imutData)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'start_month' => 'required|integer|min:1|max:12',
            'end_month' => 'required|integer|min:1|max:12',
            'show_benchmark' => 'boolean',
            'region_type_id' => 'array|nullable',
            'region_type_id.*' => 'integer|exists:region_types,id'
        ]);

        $year = (int) $request->year;
        $startMonth = (int) $request->start_month;
        $endMonth = (int) $request->end_month;
        $showBenchmark = $request->boolean('show_benchmark', false);
        $regionTypeId = $request->region_type_id;

        // Get main penilaian data
        $penilaianData = $this->getPenilaianData($imutData->id, $year, $startMonth, $endMonth);

        // Build chart series
        $series = $this->buildChartSeries($penilaianData, $year, $startMonth, $endMonth);

        // Add benchmark data if requested
        if ($showBenchmark && $imutData->categories->is_benchmark_category) {
            $benchmarkSeries = $this->getBenchmarkSeries($imutData->id, $year, $startMonth, $endMonth, $regionTypeId);
            $series = array_merge($series['series'], $benchmarkSeries);
        } else {
            $series = $series['series'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'series' => $series,
                'labels' => $this->generateLabels($year, $startMonth, $endMonth),
                'metadata' => [
                    'imut_data_id' => $imutData->id,
                    'year' => $year,
                    'period' => "{$startMonth}-{$endMonth}",
                    'has_benchmark' => $imutData->categories->is_benchmark_category,
                    'benchmark_shown' => $showBenchmark
                ]
            ]
        ]);
    }

    /**
     * Get penilaian data for specific year and month range
     */
    private function getPenilaianData(int $imutDataId, int $year, int $startMonth, int $endMonth)
    {
        return Cache::remember(
            CacheKey::imutPenilaian($imutDataId, $year, $startMonth, $endMonth),
            now()->addMinutes(30),
            fn() => DB::table('imut_penilaians')
                ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
                ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
                ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
                ->where('imut_data.id', $imutDataId)
                ->where('laporan_imuts.report_year', $year)
                ->where('laporan_imuts.report_month', '>=', $startMonth)
                ->where('laporan_imuts.report_month', '<=', $endMonth)
                ->whereNull('laporan_imuts.deleted_at')
                ->selectRaw("
                CONCAT(laporan_imuts.report_year, '-', LPAD(laporan_imuts.report_month, 2, '0')) as periode,
                laporan_imuts.report_month,
                laporan_imuts.report_year,
                SUM(imut_penilaians.numerator_value) as total_num,
                SUM(imut_penilaians.denominator_value) as total_denum,
                AVG(imut_profil.target_value) as target")
                ->groupBy('periode', 'laporan_imuts.report_month', 'laporan_imuts.report_year')
                ->orderBy('laporan_imuts.report_year')
                ->orderBy('laporan_imuts.report_month')
                ->get()
        );
    }

    /**
     * Build main chart series (Nilai IMUT & Target)
     */
    private function buildChartSeries($penilaianData, int $year, int $startMonth, int $endMonth): array
    {
        $dataNilai = [];
        $dataTarget = [];

        $labels = $this->generateLabels($year, $startMonth, $endMonth);

        // Initialize with zero values for all months in range
        foreach (array_keys($labels) as $monthKey) {
            $dataNilai[$monthKey] = 0;
            $dataTarget[$monthKey] = 0;
        }

        // Fill with actual data
        foreach ($penilaianData as $row) {
            if (!$row || !isset($row->report_month) || !isset($row->report_year)) {
                continue;
            }

            $labelKey = sprintf('%04d-%02d', $row->report_year, $row->report_month);

            if (array_key_exists($labelKey, $labels)) {
                $nilai = $row->total_denum > 0 ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
                $target = round((float) $row->target, 2);

                $dataNilai[$labelKey] = $nilai;
                $dataTarget[$labelKey] = $target;
            }
        }

        $labelKeys = array_keys($labels);

        return [
            'series' => [
                [
                    'name' => 'Nilai IMUT',
                    'type' => 'line',
                    'data' => array_map(fn($l) => $dataNilai[$l], $labelKeys),
                    'color' => '#3b82f6', // Blue
                ],
                [
                    'name' => 'Target Standar',
                    'type' => 'line',
                    'data' => array_map(fn($l) => $dataTarget[$l], $labelKeys),
                    'color' => '#f59e0b', // Amber
                ]
            ],
            'labels' => array_values($labels)
        ];
    }

    /**
     * Get benchmark series data
     */
    private function getBenchmarkSeries(int $imutDataId, int $year, int $startMonth, int $endMonth, ?array $regionTypeId = null): array
    {
        // Reference date for active period check
        $middleMonth = ceil(($startMonth + $endMonth) / 2);
        $referenceDate = Carbon::create($year, $middleMonth, 15);

        // Query benchmarks
        $benchmarkingQuery = ImutBenchmarking::query()
            ->with('regionType:id,type,display_color')
            ->forIndicator($imutDataId)
            ->activeForPeriod($referenceDate);

        // Filter by region type if specified
        if (!empty($regionTypeId)) {
            $benchmarkingQuery->forRegion($regionTypeId);
        }

        $benchmarking = $benchmarkingQuery->get();

        $labels = $this->generateLabels($year, $startMonth, $endMonth);
        $labelCount = count($labels);

        $benchmarkSeries = [];
        $defaultColors = ['#14b8a6', '#06b6d4', '#f97316', '#ec4899', '#6366f1'];

        foreach ($benchmarking as $index => $item) {
            $typeName = $item->regionType->type ?? 'Region Unknown';
            $benchmarkValue = round($item->benchmark_value, 2);

            // Create flat line with same value for all months
            $benchmarkData = array_fill(0, $labelCount, $benchmarkValue);

            $color = $item->regionType->display_color ?? $defaultColors[$index % count($defaultColors)];

            $benchmarkSeries[] = [
                'name' => "Benchmark {$typeName}",
                'type' => 'line',
                'data' => $benchmarkData,
                'color' => $color,
            ];

            // Limit to prevent clutter
            if ($index >= 2) {
                break;
            }
        }

        return $benchmarkSeries;
    }

    /**
     * Generate labels for chart x-axis
     */
    private function generateLabels(int $year, int $startMonth, int $endMonth): array
    {
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

        $labels = [];
        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $labelKey = sprintf('%04d-%02d', $year, $month);
            $labels[$labelKey] = $monthNames[$month] . ' ' . $year;
        }

        return $labels;
    }

    /**
     * Debug endpoint untuk melihat raw benchmark data
     */
    public function debugBenchmarks(Request $request, ImutData $imutData)
    {
        $request->validate([
            'year' => 'integer|min:2020|max:2030',
            'month' => 'integer|min:1|max:12'
        ]);

        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);
        $referenceDate = Carbon::create($year, $month, 15);

        $benchmarks = ImutBenchmarking::query()
            ->with(['regionType:id,type', 'imutData:id,title'])
            ->forIndicator($imutData->id)
            ->get()
            ->map(function ($benchmark) use ($referenceDate) {
                return [
                    'id' => $benchmark->id,
                    'imut_data' => $benchmark->imutData->title,
                    'region_type' => $benchmark->regionType->type ?? 'N/A',
                    'benchmark_value' => $benchmark->benchmark_value,
                    'period_start' => $benchmark->period_start?->format('Y-m-d'),
                    'period_end' => $benchmark->period_end?->format('Y-m-d'),
                    'is_active' => $benchmark->is_active,
                    'is_valid_for_date' => $benchmark->isValidForPeriod($referenceDate),
                    'reference_date' => $referenceDate->format('Y-m-d')
                ];
            });

        return response()->json([
            'success' => true,
            'imut_data' => [
                'id' => $imutData->id,
                'title' => $imutData->title,
                'is_benchmark_category' => $imutData->categories->is_benchmark_category
            ],
            'reference_date' => $referenceDate->format('Y-m-d'),
            'benchmarks' => $benchmarks
        ]);
    }
}
