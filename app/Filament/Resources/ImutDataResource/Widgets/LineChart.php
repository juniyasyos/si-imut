<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
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
                ->label('Filter Region Benchmarking')
                ->options($regionTypes)
                ->multiple()
                ->searchable()
                ->reactive()
                ->visible($is_benchmarking)
                ->placeholder('Semua Region')
                ->helperText('Filter region benchmarking yang ingin ditampilkan')
                ->columnSpan(1),

            Radio::make('show_dataLabels')
                ->label('Tampilan Nilai pada Chart')
                ->options([
                    true => 'Tampilkan Nilai',
                    false => 'Sembunyikan Nilai',
                ])
                ->default(true)
                ->reactive()
                ->inline()
                ->columnSpan(1),
        ];
    }

    protected function getOptions(): array
    {
        $showdataLabels = $this->filterFormData['show_dataLabels'] ?? true;

        // Get both series data and labels from the same source
        $chartData = $this->getChartSeriesAndLabels();
        $seriesData = $chartData['series'];
        $xLabels = $chartData['labels'];

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

    /**
     * Generate chart series data dan labels untuk grafik
     *
     * Return format:
     * [
     *   'series' => [...],  // Data series untuk chart
     *   'labels' => [...]   // Label bulan untuk X-axis
     * ]
     *
     * Konfigurasi benchmarking (warna & tipe chart) diambil dari database:
     * - Warna (color): dari field region_types.display_color
     * - Tipe chart: dari field region_types.chart_type
     * - Fallback otomatis jika data belum diset di database
     *
     * Admin dapat mengatur melalui menu: Region Type Benchmarking
     *
     * @return array Series data dan labels untuk ApexCharts
     */
    protected function getChartSeriesAndLabels(): array
    {
        $year = $this->filterFormData['year'] ?? now()->year;
        $endMonth = $this->filterFormData['end_month'] ?? now()->month;
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $imutDataId = $this->imutData->id;

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
        $labels = [];

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Build data dan labels dari hasil query penilaian
        foreach ($penilaianData as $row) {
            $labelKey = sprintf('%04d-%02d', $row->report_year, $row->report_month);
            $labelDisplay = $monthNames[$row->report_month] . ' ' . $row->report_year;

            $nilai = $row->total_denum > 0 ? round(($row->total_num / $row->total_denum) * 100, 2) : 0;
            $target = round($row->target, 2);

            $labels[$labelKey] = $labelDisplay;
            $dataNilai[$labelKey] = $nilai;
            $dataTarget[$labelKey] = $target;
        }

        // Jika tidak ada data penilaian, return empty
        if (empty($labels)) {
            return ['series' => [], 'labels' => []];
        }

        $labelKeys = array_keys($labels);
        $labelValues = array_values($labels);

        // Default colors yang konsisten
        $series = [
            [
                'name' => 'Nilai IMUT',
                'type' => 'line',
                'data' => array_map(fn($l) => $dataNilai[$l] ?? 0, $labelKeys),
                'color' => '#3b82f6', // Blue
            ],
            [
                'name' => 'Target Standar',
                'type' => 'line',
                'data' => array_map(fn($l) => $dataTarget[$l] ?? 0, $labelKeys),
                'color' => '#f59e0b', // Amber
            ],
        ];

        // Tampilkan benchmarking otomatis jika kategori adalah benchmarking
        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        if ($is_benchmarking) {
            // TIDAK menggunakan cache untuk benchmarking agar perubahan konfigurasi
            // (display_color, chart_type) langsung terlihat tanpa perlu clear cache
            $benchmarking = ImutBenchmarking::query()
                ->with('regionType:id,type,display_color,chart_type')
                ->forIndicator($imutDataId)
                ->forYearMonth($year, $endMonth)
                ->when($regionTypeId, fn($q) => $q->forRegion($regionTypeId))
                ->where('is_active', true)
                ->orderBy('region_type_id')
                ->orderBy('period_start')
                ->get();

            $regionSeries = [];

            // Proses setiap benchmark untuk mengisi data berdasarkan periode yang valid
            foreach ($benchmarking as $item) {
                $typeName = $item->regionType->type ?? 'Unknown';
                $benchmarkValue = round($item->benchmark_value, 2);

                // Loop through semua labelKeys (periode) yang ada di chart
                foreach ($labelKeys as $labelKey) {
                    // Parse labelKey format: "YYYY-MM"
                    [$labelYear, $labelMonth] = explode('-', $labelKey);
                    $labelYear = (int) $labelYear;
                    $labelMonth = (int) $labelMonth;

                    // Buat tanggal untuk label ini (akhir bulan)
                    $periodDate = \Carbon\Carbon::create($labelYear, $labelMonth, 1)->endOfMonth();

                    // Cek apakah benchmark valid untuk periode ini
                    if ($item->isValidForPeriod($periodDate)) {
                        $regionSeries[$item->region_type_id][$typeName][$labelKey] = $benchmarkValue;
                    }
                }
            }

            $colorIndex = 0;

            foreach ($regionSeries as $regionId => $seriesGroup) {
                foreach ($seriesGroup as $name => $data) {
                    if (collect($labelKeys)->contains(fn($l) => isset($data[$l]))) {
                        // Ambil region type dari database untuk mendapatkan konfigurasi tampilan
                        $regionType = $benchmarking->firstWhere('region_type_id', $regionId)?->regionType;

                        // Ambil color dari database (field: display_color)
                        // Fallback otomatis ke warna default berdasarkan nama type jika belum diset
                        $color = $regionType?->getDisplayColorWithFallback() ?? $this->getFallbackColor($colorIndex);

                        // Ambil chart type dari database (field: chart_type)
                        // Default: 'column' jika belum diset di database
                        $chartType = $regionType?->getChartTypeWithFallback() ?? 'column';

                        $series[] = [
                            'name' => $name,
                            'type' => $chartType,  // Tipe chart dari database
                            'data' => array_map(fn($l) => $data[$l] ?? null, $labelKeys),
                            'color' => $color,     // Warna dari database
                        ];

                        $colorIndex++;
                    }
                }
            }
        }

        return [
            'series' => $series,
            'labels' => $labelValues  // Gunakan label yang konsisten dengan data
        ];
    }

    /**
     * Get fallback color untuk backward compatibility
     *
     * @param int $colorIndex
     * @return string
     */
    protected function getFallbackColor(int $colorIndex): string
    {
        $fallbackColors = ['#14b8a6', '#06b6d4', '#f97316', '#ec4899', '#6366f1'];
        return $fallbackColors[$colorIndex % count($fallbackColors)];
    }
}
