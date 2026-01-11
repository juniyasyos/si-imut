<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Services\ImutBenchmarkingService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class LineChart extends ApexChartWidget
{
    protected static ?string $chartId = 'lineChart';

    protected static ?string $heading = 'Grafik Penilaian IMUT Data';

    protected int|string|array $columnSpan = 'full';

    protected static MaxWidth|string $filterFormWidth = MaxWidth::Large;

    protected static bool $isLazy = false;

    public ImutData $imutData;

    protected ImutBenchmarkingService $benchmarkingService;

    public function mount(): void
    {
        $this->benchmarkingService = app(ImutBenchmarkingService::class);
    }

    protected function hasFilterForm(): bool
    {
        return true;
    }

    protected function getFormSchema(): array
    {
        $years = LaporanImut::select('report_year as year')
            ->distinct()
            ->orderBy('report_year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        // Ensure 2025 is available if not in the list
        if (!array_key_exists(2025, $years)) {
            $years = [2025 => 2025] + $years;
        }

        $months = [
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

        // Use dummy region types for testing
        $regionTypes = [
            1 => '🌍 Nasional',
            2 => '📍 Provinsi',
        ];

        // Original code (commented for debugging)
        // $regionTypes = $this->imutData->regionTypes()->pluck('type', 'id')->toArray();

        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        return [
            Select::make('period_type')
                ->label('Jenis Periode')
                ->options([
                    'yearly' => 'Per Tahun',
                    'custom' => 'Custom Range',
                    'quarter' => 'Per Quartal',
                    'semester' => 'Per Semester',
                    'ytd' => 'Year to Date',
                ])
                ->default('yearly')
                ->live()
                ->columnSpan(2),

            Select::make('year')
                ->label('Tahun')
                ->options($years)
                ->default(2025)
                ->live()
                ->required()
                ->columnSpan(1),

            Select::make('quarter')
                ->label('Quartal')
                ->options([
                    1 => 'Q1 (Jan-Mar)',
                    2 => 'Q2 (Apr-Jun)',
                    3 => 'Q3 (Jul-Sep)',
                    4 => 'Q4 (Okt-Des)',
                ])
                ->default(ceil(now()->month / 3))
                ->live()
                ->required(fn($get) => $get('period_type') === 'quarter')
                ->visible(fn($get) => $get('period_type') === 'quarter')
                ->columnSpan(1),

            Select::make('semester')
                ->label('Semester')
                ->options([
                    1 => 'Semester 1 (Jan-Jun)',
                    2 => 'Semester 2 (Jul-Des)',
                ])
                ->default(now()->month <= 6 ? 1 : 2)
                ->live()
                ->required(fn($get) => $get('period_type') === 'semester')
                ->visible(fn($get) => $get('period_type') === 'semester')
                ->columnSpan(1),

            Select::make('start_month')
                ->label('Dari Bulan')
                ->options($months)
                ->default(1)
                ->live()
                ->visible(fn($get) => $get('period_type') === 'custom')
                ->required(fn($get) => $get('period_type') === 'custom')
                ->columnSpan(2),

            Select::make('end_month')
                ->label('Sampai Bulan')
                ->options($months)
                ->default(fn($get) => $get('period_type') === 'ytd' ? now()->month : 12)
                ->live()
                ->visible(fn($get) => in_array($get('period_type'), ['custom', 'ytd']))
                ->required(fn($get) => in_array($get('period_type'), ['custom', 'ytd']))
                ->helperText(fn($get) => $get('period_type') === 'ytd' ? 'Data dari Januari sampai bulan ini' : 'Pilih rentang bulan untuk analisis')
                ->columnSpan(2),

            Toggle::make('show_benchmark')
                ->label('Tampilkan Benchmark')
                ->helperText('Aktifkan untuk menampilkan data benchmark regional')
                ->default(false)
                ->live()
                ->visible(fn() => $is_benchmarking) // Always show for testing
                ->columnSpan(2),

            Select::make('region_type_id')
                ->label('Filter Region Benchmarking')
                ->options($regionTypes)
                ->multiple()
                ->searchable()
                ->live()
                ->visible(fn($get) => $is_benchmarking && $get('show_benchmark') && !empty($regionTypes))
                ->placeholder('Semua Region')
                ->helperText('Filter region benchmarking yang ingin ditampilkan')
                ->columnSpan(2),

            Radio::make('chart_style')
                ->label('Gaya Tampilan')
                ->options([
                    'standard' => 'Standard Line',
                    'smooth' => 'Smooth Curve',
                    'stepped' => 'Step Line',
                ])
                ->default('standard')
                ->live()
                ->inline()
                ->columnSpan(2),

            Radio::make('show_dataLabels')
                ->label('Label Data')
                ->options([
                    true => 'Tampilkan',
                    false => 'Sembunyikan',
                ])
                ->default(true)
                ->live()
                ->inline()
                ->columnSpan(1),

            Radio::make('show_trend')
                ->label('Garis Tren')
                ->options([
                    true => 'Tampilkan',
                    false => 'Sembunyikan',
                ])
                ->default(false)
                ->live()
                ->inline()
                ->columnSpan(1),
        ];
    }

    protected function getOptions(): array
    {
        $showdataLabels = $this->filterFormData['show_dataLabels'] ?? true;
        $chartStyle = $this->filterFormData['chart_style'] ?? 'standard';
        $showTrend = $this->filterFormData['show_trend'] ?? false;

        // Get both series data and labels from the same source
        $chartData = $this->getChartSeriesAndLabels();
        $seriesData = $chartData['series'];
        $xLabels = $chartData['labels'];

        if (empty($seriesData)) {
            return ApexChartConfig::noDataOptions();
        }

        // Apply chart style modifications
        foreach ($seriesData as &$series) {
            if ($series['type'] === 'line') {
                switch ($chartStyle) {
                    case 'smooth':
                        $series['curve'] = 'smooth';
                        break;
                    case 'stepped':
                        $series['curve'] = 'stepline';
                        break;
                    default:
                        $series['curve'] = 'straight';
                        break;
                }
            }
        }

        return ApexChartConfig::defaultOptions(
            series: $seriesData,
            xLabels: $xLabels,
            backgroundchart: 'transparent',
            xLabelTitle: 'Periode',
            yLabelTitle: 'Nilai (%)',
            yAxisMin: 0,
            yAxisMax: 120,
            showDataLabels: $showdataLabels
        );
    }

    protected function getChartSeriesAndLabels(): array
    {
        // Get filter values
        $year = (int) ($this->filterFormData['year'] ?? 2025);
        $periodType = $this->filterFormData['period_type'] ?? 'yearly';
        $showBenchmark = $this->filterFormData['show_benchmark'] ?? false;
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $imutDataId = $this->imutData->id;

        // Calculate date range based on period type
        $dateRange = $this->calculateDateRange($periodType);
        $startMonth = (int) $dateRange['start_month'];
        $endMonth = (int) $dateRange['end_month'];

        // Get main data
        $penilaianData = $this->getPenilaianData($imutDataId, $year, $startMonth, $endMonth);

        return $this->buildChartData($penilaianData, $showBenchmark, $regionTypeId, $year, $startMonth, $endMonth);
    }

    /**
     * Calculate date range based on period type
     */
    protected function calculateDateRange(string $periodType): array
    {
        switch ($periodType) {
            case 'quarter':
                $quarter = $this->filterFormData['quarter'] ?? ceil(now()->month / 3);
                $quarter = max(1, min(4, (int) $quarter)); // Ensure valid quarter
                return [
                    'start_month' => ($quarter - 1) * 3 + 1,
                    'end_month' => $quarter * 3
                ];

            case 'semester':
                $semester = $this->filterFormData['semester'] ?? (now()->month <= 6 ? 1 : 2);
                $semester = max(1, min(2, (int) $semester)); // Ensure valid semester
                return [
                    'start_month' => $semester === 1 ? 1 : 7,
                    'end_month' => $semester === 1 ? 6 : 12
                ];

            case 'yearly':
                return [
                    'start_month' => 1,
                    'end_month' => 12
                ];

            case 'ytd':
                $endMonth = $this->filterFormData['end_month'] ?? now()->month;
                $endMonth = max(1, min(12, (int) $endMonth)); // Ensure valid month
                return [
                    'start_month' => 1,
                    'end_month' => $endMonth
                ];

            case 'custom':
            default:
                $startMonth = $this->filterFormData['start_month'] ?? 1;
                $endMonth = $this->filterFormData['end_month'] ?? 12; // Default to full year
                $startMonth = max(1, min(12, (int) $startMonth));
                $endMonth = max(1, min(12, (int) $endMonth));

                // Ensure end month is not before start month
                if ($endMonth < $startMonth) {
                    $endMonth = 12; // Reset to full year if invalid
                }

                return [
                    'start_month' => $startMonth,
                    'end_month' => $endMonth
                ];
        }
    }

    /**
     * Get penilaian data for specific year and month range
     */
    protected function getPenilaianData(int $imutDataId, int $year, int $startMonth, int $endMonth): \Illuminate\Support\Collection
    {
        // Generate dummy data for testing
        $dummyData = collect();

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $periode = sprintf('%04d-%02d', $year, $month);

            // Generate random values
            $totalNum = rand(80, 95);
            $totalDenum = 100;
            $target = rand(85, 90);

            $dummyData->push((object) [
                'periode' => $periode,
                'report_month' => $month,
                'report_year' => $year,
                'total_num' => $totalNum,
                'total_denum' => $totalDenum,
                'target' => $target,
            ]);
        }

        return $dummyData;

        // Original query (commented for debugging)
        /*
        $result = DB::table('imut_penilaians')
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
            ->get();

        return $result;
        */
    }

    /**
     * Build chart data from penilaian data
     */
    protected function buildChartData($penilaianData, bool $showBenchmark, $regionTypeId, int $year, int $startMonth, int $endMonth): array
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

        // Build main data
        foreach ($penilaianData as $row) {
            if (!$row || !isset($row->report_month) || !isset($row->report_year)) {
                continue;
            }

            $labelKey = sprintf('%04d-%02d', $row->report_year, $row->report_month);
            $labelDisplay = $monthNames[$row->report_month] . ' ' . $row->report_year;

            $nilai = $row->total_denum > 0 ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $target = round((float) $row->target, 2);

            $labels[$labelKey] = $labelDisplay;
            $dataNilai[$labelKey] = $nilai;
            $dataTarget[$labelKey] = $target;
        }

        $labelKeys = array_keys($labels);
        $labelValues = array_values($labels);

        // Build series
        $series = [];

        // Add main data series
        $series[] = [
            'name' => 'Nilai IMUT',
            'type' => 'line',
            'data' => array_map(fn($l) => $dataNilai[$l] ?? 0, $labelKeys),
            'color' => '#3b82f6', // Blue
        ];

        // Add target series
        $series[] = [
            'name' => 'Target Standar',
            'type' => 'line',
            'data' => array_map(fn($l) => $dataTarget[$l] ?? 0, $labelKeys),
            'color' => '#f59e0b', // Amber
        ];

        // Add benchmarking data if enabled and has benchmark data
        if ($showBenchmark && $this->imutData->categories->is_benchmark_category) {
            $this->addBenchmarkingData($series, $labelKeys, $year, $startMonth, $endMonth, $regionTypeId);
        }

        return [
            'series' => $series,
            'labels' => $labelValues
        ];
    }

    /**
     * Add benchmarking data to series using the new relationship structure
     */
    protected function addBenchmarkingData(&$series, $labelKeys, int $year, int $startMonth, int $endMonth, $regionTypeId): void
    {
        // Generate dummy benchmark data for testing
        $dummyRegionTypes = [
            (object) ['id' => 1, 'type' => '🌍 Nasional', 'display_color' => '#10b981'],
            (object) ['id' => 2, 'type' => '📍 Provinsi', 'display_color' => '#8b5cf6'],
        ];

        foreach ($dummyRegionTypes as $regionType) {
            // Skip if specific region type is selected and this isn't one of them
            if ($regionTypeId && !in_array($regionType->id, (array) $regionTypeId)) {
                continue;
            }

            // Generate dummy benchmark data
            $benchmarkData = [];
            $baseValue = $regionType->id == 1 ? 88 : 85; // Different base for different regions

            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $variation = rand(-3, 3); // Random variation
                $benchmarkData[] = $baseValue + $variation;
            }

            $series[] = [
                'name' => "📊 Benchmark {$regionType->type}",
                'type' => 'line',
                'data' => $benchmarkData,
                'color' => $regionType->display_color,
                'strokeDashArray' => 5,
                'strokeWidth' => 2
            ];
        }

        /* Original code (commented for debugging)
        // Get region types for this imut data
        $query = $this->imutData->regionTypes();

        if ($regionTypeId) {
            $regionTypeIds = is_array($regionTypeId) ? $regionTypeId : [$regionTypeId];
            $query->whereIn('id', $regionTypeIds);
        }

        $regionTypes = $query->get();

        foreach ($regionTypes as $regionType) {
            // Get benchmarking data for this region type and year
            $benchmarks = $this->imutData->benchmarkings()
                ->where('region_type_id', $regionType->id)
                ->where('is_active', true)
                ->get();

            if ($benchmarks->isEmpty()) {
                continue;
            }

            // Build benchmark data for each month in range
            $benchmarkData = [];
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                // Find benchmark value for this month
                $benchmarkValue = null;
                foreach ($benchmarks as $benchmark) {
                    $periodStart = \Carbon\Carbon::parse($benchmark->period_start);
                    $periodEnd = $benchmark->period_end ? \Carbon\Carbon::parse($benchmark->period_end) : \Carbon\Carbon::create($year, 12, 31);
                    $currentMonth = \Carbon\Carbon::create($year, $month, 1);

                    if ($currentMonth->between($periodStart, $periodEnd)) {
                        $benchmarkValue = (float) $benchmark->benchmark_value;
                        break;
                    }
                }

                $benchmarkData[] = $benchmarkValue;
            }

            // Only add series if it has data
            if (!empty(array_filter($benchmarkData, fn($v) => $v !== null))) {
                $color = $regionType->display_color ?? '#10b981'; // Fallback to green

                $series[] = [
                    'name' => "📊 Benchmark {$regionType->type}",
                    'type' => 'line',
                    'data' => $benchmarkData,
                    'color' => $color,
                    'strokeDashArray' => 5,
                    'strokeWidth' => 2
                ];
            }
        }
        */
    }
}
