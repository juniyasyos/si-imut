<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Models\UnitKerja;
use App\Support\ApexChartConfig;
use App\Support\CacheKey as SupportCacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UnitKerjaChart extends ApexChartWidget
{
    protected static ?string $chartId = 'unitKerjaChart';

    protected int|string|array $columnSpan = 'full';

    protected static MaxWidth|string $filterFormWidth = MaxWidth::Large;

    protected static bool $isLazy = false;

    public ImutData $imutData;

    public UnitKerja $unitKerja;

    protected function getHeading(): ?string
    {
        $unitKerjaId = $this->filterFormData['unit_kerja_id'] ?? null;

        $unitName = UnitKerja::find($unitKerjaId)?->unit_name;

        return 'Grafik Penilaian IMUT Data ini ada dimana ya' . ($unitName ? ': ' . $unitName : '');
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

        $unitKerjaOptions = UnitKerja::pluck('unit_name', 'id')->toArray();

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

            Select::make('unit_kerja_id')
                ->label('Unit Kerja')
                ->options($unitKerjaOptions)
                ->default($this->unitKerja->id)
                ->searchable()
                ->required()
                ->reactive()
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

    /**
     * Generate chart series data untuk grafik per unit kerja
     *
     * Konfigurasi benchmarking (warna & tipe chart) diambil dari database:
     * - Warna (color): dari field region_types.display_color
     * - Tipe chart: dari field region_types.chart_type
     * - Fallback otomatis jika data belum diset di database
     *
     * Admin dapat mengatur melalui menu: Region Type Benchmarking
     *
     * @return array Series data untuk ApexCharts
     */
    protected function getChartSeries(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $endMonth = $this->filterFormData['end_month'] ?? now()->month;
        $unitKerjaId = $this->filterFormData['unit_kerja_id'] ?? null;
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
            // TIDAK menggunakan cache untuk benchmarking agar perubahan konfigurasi
            // (display_color, chart_type) langsung terlihat tanpa perlu clear cache
            $benchmarking = ImutBenchmarking::query()
                ->with('regionType:id,type,display_color,chart_type')
                ->forIndicator($imutDataId)
                ->forYearMonth($year, $endMonth)
                ->where('is_active', true)
                ->orderBy('region_type_id')
                ->orderBy('period_start')
                ->get();

            $benchmarkGrouped = $benchmarking->groupBy(fn($item) => sprintf('%04d-%02d', $item->year, $item->month));
            $regionSeries = [];
            $regionTypeMap = []; // Map untuk menyimpan region type object
            $labelMap = [];

            foreach ($benchmarkGrouped as $periodeKey => $items) {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $periodeKey);
                $labelKey = $date->format('Y-m');
                $labelDisplay = $monthNames[$date->month] . ' ' . $date->year;
                $labelMap[$labelKey] = $labelDisplay;

                foreach ($items as $item) {
                    // Validate if benchmark is valid for this period
                    $periodDate = $date->endOfMonth();
                    if (!$item->isValidForPeriod($periodDate)) {
                        continue;
                    }

                    $type = $item->regionType->type ?? 'Unknown';
                    $regionSeries[$type][$labelKey] = round($item->benchmark_value, 2);

                    // Simpan region type untuk akses color dan chart type nanti
                    if (!isset($regionTypeMap[$type])) {
                        $regionTypeMap[$type] = $item->regionType;
                    }
                }
            }

            $colorIndex = 0;

            foreach ($regionSeries as $regionName => $seriesGroup) {
                if (collect($labels)->contains(fn($l) => isset($seriesGroup[$l]))) {
                    // Ambil region type dari database untuk mendapatkan konfigurasi tampilan
                    $regionType = $regionTypeMap[$regionName] ?? null;

                    // Ambil color dari database (field: display_color)
                    // Fallback otomatis ke warna default berdasarkan nama type jika belum diset
                    $color = $regionType?->getDisplayColorWithFallback() ?? $this->getFallbackColor($colorIndex);

                    // Ambil chart type dari database (field: chart_type)
                    // Default: 'column' jika belum diset di database
                    $chartType = $regionType?->getChartTypeWithFallback() ?? 'column';

                    $series[] = [
                        'name' => $regionName,
                        'type' => $chartType,  // Tipe chart dari database
                        'data' => array_map(fn($l) => $seriesGroup[$l] ?? null, $labels),
                        'color' => $color,     // Warna dari database
                    ];

                    $colorIndex++;
                }
            }
        }

        return $series;
    }

    /**
     * Get fallback color untuk backward compatibility
     *
     * @param int $index
     * @return string
     */
    protected function getFallbackColor(int $index): string
    {
        $fallbackColors = ['#14b8a6', '#06b6d4', '#f97316', '#ec4899', '#6366f1'];
        return $fallbackColors[$index % count($fallbackColors)];
    }
}
