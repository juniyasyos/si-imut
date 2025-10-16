<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Domains\Imut\Models\ImutBenchmarking;
use App\Domains\Reporting\Models\LaporanImut;
use App\Models\RegionType;
use App\Domains\Organization\Models\UnitKerja;
use App\Support\ApexChartConfig;
use App\Support\CacheKey as SupportCacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutDataUnitKerjaGrafikOverview extends ApexChartWidget
{
    protected static ?string $chartId = 'imutDataUnitKerjaGrafikOverview';

    protected int|string|array $columnSpan = 'full';

    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;

    protected static bool $isLazy = false;

    public \App\Domains\Imut\Models\ImutData $imutData;

    public \App\Domains\Organization\Models\UnitKerja $unitKerja;

    protected function getHeading(): ?string
    {
        $unitKerjaId = $this->filterFormData['unit_kerja_id'] ?? null;

        $unitName = UnitKerja::find($unitKerjaId)?->unit_name;

        return 'Grafik Penilaian IMUT Data' . ($unitName ? ': ' . $unitName : '');
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

        $unitKerjaOptions = UnitKerja::pluck('unit_name', 'id')->toArray();

        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        return [
            Section::make('Filter Data')
                ->schema([
                    Select::make('year')->label('Tahun')->options($years)->default(now()->year)->reactive(),
                    Select::make('end_month')->label('Sampai Bulan')->options($months)->default(now()->month)->reactive()
                        ->helperText('Tampilkan data dari Januari sampai bulan yang dipilih'),
                    Select::make('unit_kerja_id')->label('Unit Kerja')->options($unitKerjaOptions)->default($this->unitKerja->id)->searchable()->required()->reactive(),
                    Checkbox::make('show_benchmarking')->label('Tampilkan Benchmarking')->default(false)->reactive()->visible($is_benchmarking),
                    Checkbox::make('show_dataLabels')->label('Tampilkan Nilai')->default(true)->reactive(),
                ])
                ->columns(3),

            Section::make('Konfigurasi Chart Utama')
                ->schema([
                    Group::make([
                        Select::make('nilai_type')
                            ->label('Tipe Nilai IMUT')
                            ->options(['line' => 'Line', 'column' => 'Column'])
                            ->default('line')
                            ->reactive(),

                        ColorPicker::make('color_nilai')
                            ->label('Warna Nilai IMUT')
                            ->default('#3b82f6')
                            ->reactive(),
                    ])->columns(2),

                    Group::make([
                        Select::make('target_type')
                            ->label('Tipe Target')
                            ->options(['line' => 'Line', 'column' => 'Column'])
                            ->default('line')
                            ->reactive(),

                        ColorPicker::make('color_target')
                            ->label('Warna Target')
                            ->default('#f59e0b')
                            ->reactive(),
                    ])->columns(2),
                ])
                ->columns(1),

            Section::make('Benchmarking Series')
                ->schema(
                    collect($regionTypes)->map(function ($name, $id) {
                        return Fieldset::make($name)
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make("benchmark_types.$id")
                                            ->label('Tipe')
                                            ->options(['line' => 'Line', 'column' => 'Column'])
                                            ->default('column')
                                            ->reactive(),

                                        ColorPicker::make("benchmark_colors.$id")
                                            ->label('Warna')
                                            ->default('#' . substr(md5($name), 0, 6))
                                            ->reactive(),
                                    ])
                                    ->columns(2),
                            ]);
                    })->values()->toArray()
                )
                ->columns(2)
                ->collapsed(),
        ];
    }

    protected function getOptions(): array
    {
        $chartType = $this->filterFormData['chart_type'] ?? 'mixed';
        $showdataLabels = $this->filterFormData['show_dataLabels'] ?? true;

        $seriesData = $this->getChartSeries($chartType);
        $xLabels = $this->getMonthLabels();

        if (empty($seriesData)) {
            return ApexChartConfig::noDataOptions();
        }

        return ApexChartConfig::defaultOptions(
            series: $seriesData,
            xLabels: $xLabels,
            xLabelTitle: 'Periode',
            yLabelTitle: 'Nilai (%)',
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

    protected function getChartSeries(string $chartType = 'line'): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $endMonth = $this->filterFormData['end_month'] ?? now()->month;
        $unitKerjaId = $this->filterFormData['unit_kerja_id'] ?? null;
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $showBenchmarking = $this->filterFormData['show_benchmarking'] ?? true;
        $imutDataId = $this->imutData->id;

        $penilaianData = Cache::remember(
            SupportCacheKey::imutPenilaianImutDataUnitKerja($imutDataId, $year, $unitKerjaId, $endMonth),
            now()->addMinutes(30),
            function () use ($imutDataId, $year, $endMonth, $unitKerjaId) {
                return DB::table('imut_penilaians')
                    ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                    ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
                    ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
                    ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
                    ->where('imut_data.id', $imutDataId)
                    ->where('laporan_imuts.report_year', $year)
                    ->where('laporan_imuts.report_month', '<=', $endMonth)
                    ->when($unitKerjaId, function ($query) use ($unitKerjaId) {
                        $query->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId);
                    })
                    ->whereNull('laporan_imuts.deleted_at')
                    ->selectRaw("
                        CONCAT(laporan_imuts.report_year, '-', LPAD(laporan_imuts.report_month, 2, '0')) as periode,
                        laporan_imuts.report_month,
                        laporan_imuts.report_year,
                        SUM(imut_penilaians.numerator_value) as total_num,
                        SUM(imut_penilaians.denominator_value) as total_denum,
                        AVG(imut_profil.target_value) as target
                    ")
                    ->groupBy('periode', 'laporan_imuts.report_month', 'laporan_imuts.report_year')
                    ->orderBy('laporan_imuts.report_year')
                    ->orderBy('laporan_imuts.report_month')
                    ->get();
            }
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
            $nilai = ($row->total_denum > 0) ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $target = round($row->target, 2);

            $labelKey = \Carbon\Carbon::parse($row->periode . '-01')->format('Y-m');
            $labelDisplay = $label;
            $labels[$labelKey] = $labelDisplay;
            $dataNilai[$labelKey] = $nilai;
            $dataTarget[$labelKey] = $target;
        }

        $labels = array_keys($dataNilai);

        $tipeNilai = $this->filterFormData['nilai_type'] ?? ($chartType === 'bar' || $chartType === 'mixed' ? 'column' : 'line');
        $tipeTarget = $this->filterFormData['target_type'] ?? 'line';

        $series = [
            [
                'name' => 'Nilai IMUT',
                'type' => $tipeNilai,
                'data' => array_map(fn($l) => $dataNilai[$l] ?? 0, $labels),
                'color' => $this->filterFormData['color_nilai'] ?? '#3b82f6',
            ],
            [
                'name' => 'Target Standar',
                'type' => $tipeTarget,
                'data' => array_map(fn($l) => $dataTarget[$l] ?? 0, $labels),
                'color' => $this->filterFormData['color_target'] ?? '#f59e0b',
            ],
        ];

        if ($showBenchmarking) {
            $benchmarkKey = SupportCacheKey::imutBenchmarking($year, $regionTypeId, $imutDataId, $endMonth);
            $benchmarking = Cache::remember(
                $benchmarkKey,
                now()->addMinutes(30),
                function () use ($year, $endMonth, $regionTypeId, $imutDataId) {
                    return ImutBenchmarking::query()
                        ->with('regionType:id,type')
                        ->select('year', 'month', 'benchmark_value', 'region_type_id', 'imut_data_id')
                        ->where('year', $year)
                        ->where('month', '<=', $endMonth)
                        ->where('imut_data_id', $imutDataId)
                        ->when($regionTypeId, fn($q) => $q->whereIn('region_type_id', $regionTypeId))
                        ->get();
                }
            );

            $benchmarkGrouped = $benchmarking->groupBy(fn($item) => sprintf('%04d-%02d', $item->year, $item->month));
            $regionSeries = [];
            $labelMap = [];

            foreach ($benchmarkGrouped as $periodeKey => $items) {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $periodeKey);
                $labelKey = $date->format('Y-m');
                $labelDisplay = $monthNames[$date->month] . ' ' . $date->year;
                $labelMap[$labelKey] = $labelDisplay;

                foreach ($items as $item) {
                    $type = $item->regionType->type ?? 'Unknown';
                    $regionSeries[$type][$labelKey] = round($item->benchmark_value, 2);
                }
            }

            foreach ($regionSeries as $regionId => $seriesGroup) {
                if (collect($labels)->contains(fn($l) => isset($seriesGroup[$l]))) {
                    $series[] = [
                        'name' => $regionId,
                        'type' => $this->filterFormData['benchmark_types'][$regionId] ?? 'column',
                        'data' => array_map(fn($l) => $seriesGroup[$l] ?? null, $labels),
                        'color' => $this->filterFormData['benchmark_colors'][$regionId] ?? '#' . substr(md5($regionId), 0, 6),
                    ];
                }
            }
        }

        return $series;
    }
}
