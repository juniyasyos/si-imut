<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Checkbox;
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

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $regionTypes = RegionType::pluck('type', 'id')->toArray();

        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        return [
            Select::make('year')
                ->label('Tahun')
                ->options($years)
                ->default(now()->year)
                ->reactive()
                ->required()
                ->columnSpan(1),

            Select::make('end_month')
                ->label('Sampai Bulan')
                ->options($months)
                ->default(now()->month)
                ->reactive()
                ->helperText('Tampilkan data dari Januari sampai bulan yang dipilih')
                ->required()
                ->columnSpan(1),

            Select::make('region_type_id')
                ->label('Benchmarking Region')
                ->options($regionTypes)
                ->multiple()
                ->searchable()
                ->reactive()
                ->visible($is_benchmarking)
                ->placeholder('Pilih region untuk ditampilkan')
                ->columnSpan(1),

            Checkbox::make('show_benchmarking')
                ->label('Tampilkan Benchmarking')
                ->default(true)
                ->reactive()
                ->visible($is_benchmarking)
                ->inline(false)
                ->columnSpan(1),

            Checkbox::make('show_dataLabels')
                ->label('Tampilkan Nilai pada Chart')
                ->default(true)
                ->reactive()
                ->inline(false)
                ->columnSpan(1),
        ];
    }

    protected function getOptions(): array
    {
        $showdataLabels = $this->filterFormData['show_dataLabels'] ?? true;

        $seriesData = $this->getChartSeries();
        $xLabels = $this->getMonthLabels();

        if (empty($seriesData)) {
            return ApexChartConfig::noDataOptions();
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


    protected function getMonthLabels(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $endMonth = $this->filterFormData['end_month'] ?? now()->month;

        return LaporanImut::where('report_year', $year)
            ->where('report_month', '<=', $endMonth)
            ->orderBy('report_month')
            ->get()
            ->map(fn($laporan) => $laporan->period_name)
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getChartSeries(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $endMonth = $this->filterFormData['end_month'] ?? now()->month;
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $imutDataId = $this->imutData->id;
        $showBenchmarking = $this->filterFormData['show_benchmarking'] ?? true;

        $penilaianData = Cache::remember(
            CacheKey::imutPenilaian($imutDataId, $year, $endMonth),
            now()->addMinutes(30),
            fn() => DB::table('imut_penilaians')
                ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
                ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
                ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
                ->where('imut_data.id', $imutDataId)
                ->where('laporan_imuts.report_year', $year)
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

        $dataNilai = [];
        $dataTarget = [];

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($penilaianData as $row) {
            $label = $monthNames[$row->report_month] . ' ' . $row->report_year;
            $nilai = $row->total_denum > 0 ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $target = round($row->target, 2);

            $dataNilai[$label] = $nilai;
            $dataTarget[$label] = $target;
        }

        $labels = array_keys($dataNilai);

        // Default colors yang konsisten
        $series = [
            [
                'name' => 'Nilai IMUT',
                'type' => 'line',
                'data' => array_map(fn($l) => $dataNilai[$l] ?? 0, $labels),
                'color' => '#3b82f6', // Blue
            ],
            [
                'name' => 'Target Standar',
                'type' => 'line',
                'data' => array_map(fn($l) => $dataTarget[$l] ?? 0, $labels),
                'color' => '#f59e0b', // Amber
            ],
        ];

        if ($showBenchmarking) {
            $benchmarkKey = CacheKey::imutBenchmarking($year, $regionTypeId, $imutDataId, $endMonth);
            $benchmarking = Cache::remember(
                $benchmarkKey,
                now()->addMinutes(30),
                fn() => ImutBenchmarking::query()
                    ->with('regionType:id,type')
                    ->forIndicator($imutDataId)
                    ->forYearMonth($year, $endMonth)
                    ->when($regionTypeId, fn($q) => $q->forRegion($regionTypeId))
                    ->where('is_active', true)
                    ->orderBy('region_type_id')
                    ->orderBy('period_start')
                    ->get()
            );

            $benchmarkGrouped = $benchmarking->groupBy(fn($item) => sprintf('%04d-%02d', $item->year, $item->month));
            $regionSeries = [];

            // Default colors untuk benchmarking
            $benchmarkColors = [
                'Nasional' => '#10b981', // Green
                'Provinsi' => '#8b5cf6', // Purple
                'Rumah Sakit' => '#ef4444', // Red
            ];

            foreach ($benchmarkGrouped as $periodeKey => $items) {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $periodeKey);
                $label = $monthNames[$date->month] . ' ' . $date->year;

                foreach ($items as $item) {
                    // Validate if benchmark is valid for this period
                    $periodDate = $date->endOfMonth();
                    if (!$item->isValidForPeriod($periodDate)) {
                        continue;
                    }

                    $typeName = $item->regionType->type ?? 'Unknown';
                    // Remove emoji from type name for comparison
                    $cleanTypeName = trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $typeName));

                    $regionSeries[$item->region_type_id][$typeName][$label] = round($item->benchmark_value, 2);
                }
            }

            $colorIndex = 0;
            $fallbackColors = ['#14b8a6', '#06b6d4', '#f97316', '#ec4899', '#6366f1'];

            foreach ($regionSeries as $regionId => $seriesGroup) {
                foreach ($seriesGroup as $name => $data) {
                    if (collect($labels)->contains(fn($l) => isset($data[$l]))) {
                        // Get color from predefined or generate
                        $cleanName = trim(preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $name));
                        $color = $benchmarkColors[$cleanName] ?? $fallbackColors[$colorIndex % count($fallbackColors)];

                        $series[] = [
                            'name' => $name,
                            'type' => 'column',
                            'data' => array_map(fn($l) => $data[$l] ?? null, $labels),
                            'color' => $color,
                        ];

                        $colorIndex++;
                    }
                }
            }
        }

        return $series;
    }
}
