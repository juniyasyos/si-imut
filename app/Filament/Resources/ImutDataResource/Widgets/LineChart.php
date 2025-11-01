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

        // Tampilkan benchmarking otomatis jika kategori adalah benchmarking
        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        if ($is_benchmarking) {
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

            $regionSeries = [];

            // Proses setiap benchmark untuk mengisi semua bulan dalam rentang validitasnya
            foreach ($benchmarking as $item) {
                $typeName = $item->regionType->type ?? 'Unknown';
                $benchmarkValue = round($item->benchmark_value, 2);

                // Loop through semua label (bulan) yang ada di chart
                foreach ($labels as $label) {
                    // Parse label untuk mendapatkan bulan dan tahun
                    // Format label: "Januari 2024", "Februari 2024", etc.
                    foreach ($monthNames as $monthNum => $monthName) {
                        if (str_starts_with($label, $monthName)) {
                            $labelYear = (int) substr($label, strlen($monthName) + 1);
                            $labelMonth = $monthNum;

                            // Buat tanggal untuk label ini (akhir bulan)
                            $periodDate = \Carbon\Carbon::create($labelYear, $labelMonth, 1)->endOfMonth();

                            // Cek apakah benchmark valid untuk periode ini
                            if ($item->isValidForPeriod($periodDate)) {
                                $regionSeries[$item->region_type_id][$typeName][$label] = $benchmarkValue;
                            }
                            break;
                        }
                    }
                }
            }

            $colorIndex = 0;

            foreach ($regionSeries as $regionId => $seriesGroup) {
                foreach ($seriesGroup as $name => $data) {
                    if (collect($labels)->contains(fn($l) => isset($data[$l]))) {
                        // Ambil region type untuk mendapatkan color dan chart type
                        $regionType = $benchmarking->firstWhere('region_type_id', $regionId)?->regionType;

                        // Get color dari database atau fallback
                        $color = $regionType?->getDisplayColorWithFallback() ?? $this->getFallbackColor($colorIndex);

                        // Get chart type dari database atau fallback ke column
                        $chartType = $regionType?->getChartTypeWithFallback() ?? 'column';

                        $series[] = [
                            'name' => $name,
                            'type' => $chartType,
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
