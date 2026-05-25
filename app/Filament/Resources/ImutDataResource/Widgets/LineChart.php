<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use Filament\Support\Enums\Width;
use Carbon\Carbon;
use Filament\Schemas\Components\Section;
use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\LaporanImut;
use App\Models\RegionType;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class LineChart extends ApexChartWidget
{
    protected static ?string $chartId = 'lineChart';

    protected static ?string $heading = 'Grafik Penilaian IMUT Data';

    protected static ?string $description = 'Pilih filter yang diinginkan dan tekan tombol "Filter" untuk menerapkan perubahan';

    protected int|string|array $columnSpan = 'full';

    protected static Width|string $filterFormWidth = Width::Large;

    protected static bool $isLazy = false;

    public ImutData $imutData;

    protected function hasFilterForm(): bool
    {
        return true;
    }

    /**
     * Determine if the filter form should be submitted automatically or manually.
     */
    protected function shouldSubmitFilterFormAutomatically(): bool
    {
        return false; // Require manual submit
    }

    protected function getFormSchema(): array
    {
        // Years available from laporan_imuts (already ordered desc)
        $years = LaporanImut::select('report_year as year')
            ->distinct()
            ->orderBy('report_year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        // Human-friendly month labels
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        $quarters = [
            'Q1' => 'Q1 (Jan - Mar)',
            'Q2' => 'Q2 (Apr - Jun)',
            'Q3' => 'Q3 (Jul - Sep)',
            'Q4' => 'Q4 (Okt - Des)'
        ];

        $semesters = [
            'S1' => 'Semester 1 (Jan - Jun)',
            'S2' => 'Semester 2 (Jul - Des)'
        ];

        // Default behaviour: use custom-range with end = current month (if available in laporan_imuts)
        // and start = 6 months before end. If calculated months don't exist in laporan_imuts,
        // fall back to the closest available periods (or to now() when no laporan exists).
        $now = now();

        // Latest overall report (fallback)
        $latestOverall = LaporanImut::orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->first();

        // Prefer current month/year when available in laporan_imuts for end period
        $endMonthCandidate = LaporanImut::where('report_year', $now->year)
            ->where('report_month', '<=', $now->month)
            ->max('report_month');

        if ($endMonthCandidate) {
            $defaultEndYear = (int) $now->year;
            $defaultEndMonth = (int) $endMonthCandidate;
        } elseif ($latestOverall) {
            $defaultEndYear = (int) $latestOverall->report_year;
            $defaultEndMonth = (int) $latestOverall->report_month;
        } else {
            $defaultEndYear = (int) $now->year;
            $defaultEndMonth = (int) $now->month;
        }

        // Desired start = 6 months before selected end (inclusive)
        $desiredStart = Carbon::create($defaultEndYear, $defaultEndMonth, 1)->subMonths(6);

        // Find the closest available laporan_imut on or before desiredStart
        $startRecord = LaporanImut::where(function ($q) use ($desiredStart) {
            $q->where('report_year', '<', $desiredStart->year)
                ->orWhere(function ($q2) use ($desiredStart) {
                    $q2->where('report_year', '=', $desiredStart->year)
                        ->where('report_month', '<=', $desiredStart->month);
                });
        })
            ->orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->first();

        if ($startRecord) {
            $defaultStartYear = (int) $startRecord->report_year;
            $defaultStartMonth = (int) $startRecord->report_month;
        } elseif ($latestOverall) {
            // If nothing exists before desired start, pick the earliest available record
            $earliest = LaporanImut::orderBy('report_year', 'asc')
                ->orderBy('report_month', 'asc')
                ->first();

            if ($earliest) {
                $defaultStartYear = (int) $earliest->report_year;
                $defaultStartMonth = (int) $earliest->report_month;
            } else {
                // no laporan at all -> fallback to end period
                $defaultStartYear = $defaultEndYear;
                $defaultStartMonth = $defaultEndMonth;
            }
        } else {
            $defaultStartYear = $defaultEndYear;
            $defaultStartMonth = $defaultEndMonth;
        }

        // Ensure start is not after end
        if (
            $defaultStartYear > $defaultEndYear ||
            ($defaultStartYear === $defaultEndYear && $defaultStartMonth > $defaultEndMonth)
        ) {
            $defaultStartYear = $defaultEndYear;
            $defaultStartMonth = $defaultEndMonth;
        }

        // Keep quarter/semester/year defaults consistent with the computed end period
        $defaultQuarter = 'Q' . (int) ceil($defaultEndMonth / 3);
        $defaultSemester = $defaultEndMonth <= 6 ? 'S1' : 'S2';

        // Provide a sensible default year for other selectors (use end year)
        $defaultYear = $defaultEndYear;

        $is_benchmarking = $this->imutData->categories->is_benchmark_category;

        // Enhanced region options with grouping
        $regionOptions = $this->getEnhancedRegionOptions();

        return [
            // Filter Mode Selector
            Section::make('Mode Filter Periode')
                ->description('Pilih mode filter periode yang ingin digunakan untuk menampilkan data pada grafik.')
                ->columns(1)
                ->collapsed(true)
                ->schema([
                    Radio::make('filter_mode')
                        ->label('Mode Filter Periode')
                        ->options([
                            'custom' => '📅 Rentang Kustom',
                            'quarter' => '📊 Quarter/Kuartal',
                            'semester' => '📋 Semester',
                            'yearly' => '📆 Tahunan'
                        ])
                        ->default('custom')
                        ->live()
                        ->columnSpan('full')
                        ->helperText('Pilih mode filter periode yang ingin digunakan'),
                ]),

            Section::make('Pengaturan Periode')
                ->description('Atur rentang waktu data yang ingin ditampilkan pada grafik.')
                ->columns(2)
                ->schema([
                    // Custom Range Filters (conditional)
                    Select::make('start_month')
                        ->label('Bulan Mulai')
                        ->options(fn($get) => $this->getMonthsForYearOptions($get('start_year') ?? $defaultStartYear))
                        ->default($defaultStartMonth)
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'custom')
                        ->columnSpan(1),

                    Select::make('start_year')
                        ->label('Tahun Mulai')
                        ->options($years)
                        ->default($defaultStartYear)
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'custom')
                        ->columnSpan(1),

                    Select::make('end_month')
                        ->label('Bulan Selesai')
                        ->options(fn($get) => $this->getMonthsForYearOptions($get('end_year') ?? $defaultEndYear))
                        ->default($defaultEndMonth)
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'custom')
                        ->columnSpan(1),

                    Select::make('end_year')
                        ->label('Tahun Selesai')
                        ->options($years)
                        ->default($defaultEndYear)
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'custom')
                        ->columnSpan(1),

                    // Quarter Filters (conditional)
                    Select::make('quarter_year')
                        ->label('Tahun')
                        ->options($years)
                        ->default($defaultYear)
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'quarter')
                        ->columnSpan(1),

                    Select::make('quarters')
                        ->label('Pilih Quarter')
                        ->options($quarters)
                        ->default([$defaultQuarter])
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'quarter')
                        ->helperText('Pilih satu atau lebih quarter yang ingin ditampilkan')
                        ->columnSpan(1),

                    // Semester Filters (conditional)
                    Select::make('semester_year')
                        ->label('Tahun')
                        ->options($years)
                        ->default($defaultYear)
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'semester')
                        ->columnSpan(1),

                    Select::make('semesters')
                        ->label('Pilih Semester')
                        ->options($semesters)
                        ->default([$defaultSemester])
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'semester')
                        ->helperText('Pilih satu atau lebih semester yang ingin ditampilkan')
                        ->columnSpan(1),

                    // Yearly Filter (conditional)
                    Select::make('yearly_years')
                        ->label('Pilih Tahun')
                        ->options($years)
                        ->multiple()
                        ->default([$defaultYear])
                        ->required()
                        ->visible(fn($get) => $get('filter_mode') === 'yearly')
                        ->helperText('Pilih satu atau lebih tahun untuk ditampilkan')
                        ->columnSpan(1),
                ]),

            // Enhanced Regional Filter
            Select::make('region_type_id')
                ->label('Filter Region Benchmarking')
                ->options($regionOptions)
                ->multiple()
                ->searchable()
                ->preload()
                ->visible($is_benchmarking)
                ->placeholder('Semua Region Benchmarking')
                ->helperText('Filter region benchmarking yang ingin ditampilkan. Kosong = tampilkan semua')
                ->columnSpan(1),

            // Display Options
            Radio::make('show_dataLabels')
                ->label('Tampilan Nilai pada Chart')
                ->options([
                    true => '📊 Tampilkan Nilai',
                    false => '🔍 Sembunyikan Nilai',
                ])
                ->default(true)
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
        // Get date range from new filter system
        [$startYear, $startMonth, $endYear, $endMonth] = $this->getFilterDateRange();
        $regionTypeId = $this->filterFormData['region_type_id'] ?? null;
        $imutDataId = $this->imutData->id;

        $penilaianData = DB::table('imut_penilaians')
            ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
            ->join('imut_profil', 'imut_profil.id', '=', 'imut_penilaians.imut_profil_id')
            ->join('imut_data', 'imut_data.id', '=', 'imut_profil.imut_data_id')
            ->where('imut_data.id', $imutDataId)
            ->where(function ($query) use ($startYear, $startMonth, $endYear, $endMonth) {
                // Get specific months for quarter/semester filtering
                $specificMonths = $this->getSpecificMonthsForFilter();

                if (!empty($specificMonths)) {
                    // Use specific months filtering for quarters/semesters
                    $query->where('laporan_imuts.report_year', '=', $startYear)
                        ->whereIn('laporan_imuts.report_month', $specificMonths);
                } else {
                    // Handle range-based filtering (custom and yearly)
                    if ($startYear === $endYear) {
                        // Single year range
                        $query->where('laporan_imuts.report_year', '=', $startYear)
                            ->where('laporan_imuts.report_month', '>=', $startMonth)
                            ->where('laporan_imuts.report_month', '<=', $endMonth);
                    } else {
                        // Multi-year date ranges
                        $query->where(function ($q) use ($startYear, $startMonth, $endYear, $endMonth) {
                            $q->where('laporan_imuts.report_year', '>', $startYear)
                                ->where('laporan_imuts.report_year', '<', $endYear);
                        })
                            ->orWhere(function ($q) use ($startYear, $startMonth) {
                                $q->where('laporan_imuts.report_year', '=', $startYear)
                                    ->where('laporan_imuts.report_month', '>=', $startMonth);
                            })
                            ->orWhere(function ($q) use ($endYear, $endMonth) {
                                $q->where('laporan_imuts.report_year', '=', $endYear)
                                    ->where('laporan_imuts.report_month', '<=', $endMonth);
                            });
                    }
                }
            })
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

        $dataNilai = [];
        $dataTarget = [];
        $labels = [];

        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
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
                ->where(function ($query) use ($startYear, $startMonth, $endYear, $endMonth) {
                    // Get specific months for quarter/semester filtering
                    $specificMonths = $this->getSpecificMonthsForFilter();

                    if (!empty($specificMonths)) {
                        // Use specific months filtering for quarters/semesters
                        $query->whereYear('period_start', '=', $startYear)
                            ->whereIn(DB::raw('MONTH(period_start)'), $specificMonths);
                    } else {
                        // Handle range-based filtering (custom and yearly)
                        if ($startYear === $endYear) {
                            // Single year range
                            $query->whereYear('period_start', '=', $startYear)
                                ->whereMonth('period_start', '>=', $startMonth)
                                ->whereMonth('period_start', '<=', $endMonth);
                        } else {
                            // Multi-year date ranges
                            $query->where(function ($q) use ($startYear, $startMonth, $endYear, $endMonth) {
                                $q->whereYear('period_start', '>=', $startYear)
                                    ->whereYear('period_start', '<=', $endYear)
                                    ->where(function ($q2) use ($startYear, $startMonth, $endYear, $endMonth) {
                                        $q2->where(function ($q3) use ($startYear, $startMonth) {
                                            $q3->whereYear('period_start', '=', $startYear)
                                                ->whereMonth('period_start', '>=', $startMonth);
                                        })
                                            ->orWhere(function ($q3) use ($endYear, $endMonth) {
                                                $q3->whereYear('period_start', '=', $endYear)
                                                    ->whereMonth('period_start', '<=', $endMonth);
                                            })
                                            ->orWhere(function ($q3) use ($startYear, $endYear) {
                                                $q3->whereYear('period_start', '>', $startYear)
                                                    ->whereYear('period_start', '<', $endYear);
                                            });
                                    });
                            });
                        }
                    }
                })
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
                    $periodDate = Carbon::create($labelYear, $labelMonth, 1)->endOfMonth();

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
     * Get enhanced region options with grouping and better UX
     *
     * @return array
     */
    protected function getEnhancedRegionOptions(): array
    {
        if (!$this->imutData->categories->is_benchmark_category) {
            return [];
        }

        // Get all region types that have benchmarking data for this indicator
        $regionTypes = RegionType::query()
            ->whereHas('benchmarkings', function ($query) {
                $query->where('imut_data_id', $this->imutData->id)
                    ->where('is_active', true);
            })
            ->orderBy('type')
            ->get()
            ->mapWithKeys(function ($region) {
                // Create better display text with emoji indicators
                $displayText = $region->type;

                // Add visual indicators based on type
                if (str_contains(strtolower($region->type), 'nasional')) {
                    $displayText = "🇮🇩 " . $displayText;
                } elseif (str_contains(strtolower($region->type), 'provinsi')) {
                    $displayText = "🏛️ " . $displayText;
                } elseif (str_contains(strtolower($region->type), 'rumah sakit')) {
                    $displayText = "🏥 " . $displayText;
                } else {
                    $displayText = "📍 " . $displayText;
                }

                return [$region->id => $displayText];
            })
            ->toArray();

        return $regionTypes;
    }

    /**
     * Get specific months for quarter/semester filtering
     * Returns empty array for custom/yearly (use range filtering instead)
     *
     * @return array
     */
    protected function getSpecificMonthsForFilter(): array
    {
        $mode = $this->filterFormData['filter_mode'] ?? 'custom';

        switch ($mode) {
            case 'quarter':
                $quarters = $this->filterFormData['quarters'] ?? ['Q1'];

                // Ensure quarters is always an array
                if (!is_array($quarters)) {
                    $quarters = [$quarters];
                }

                $quarterMonths = [
                    'Q1' => [1, 2, 3],
                    'Q2' => [4, 5, 6],
                    'Q3' => [7, 8, 9],
                    'Q4' => [10, 11, 12]
                ];

                $allMonths = [];
                foreach ($quarters as $quarter) {
                    $allMonths = array_merge($allMonths, $quarterMonths[$quarter]);
                }

                return array_unique($allMonths);

            case 'semester':
                $semesters = $this->filterFormData['semesters'] ?? ['S1'];

                // Ensure semesters is always an array
                if (!is_array($semesters)) {
                    $semesters = [$semesters];
                }

                $semesterMonths = [
                    'S1' => [1, 2, 3, 4, 5, 6],
                    'S2' => [7, 8, 9, 10, 11, 12]
                ];

                $allMonths = [];
                foreach ($semesters as $semester) {
                    $allMonths = array_merge($allMonths, $semesterMonths[$semester]);
                }

                return array_unique($allMonths);

            default:
                // For custom and yearly, use range filtering
                return [];
        }
    }

    /**
     * Convert filter data to date ranges based on selected mode
     *
     * @return array [start_year, start_month, end_year, end_month]
     */
    protected function getFilterDateRange(): array
    {
        $mode = $this->filterFormData['filter_mode'] ?? 'custom';

        switch ($mode) {
            case 'quarter':
                $year = $this->filterFormData['quarter_year'] ?? now()->year;
                $quarters = $this->filterFormData['quarters'] ?? ['Q1'];

                // Ensure quarters is always an array
                if (!is_array($quarters)) {
                    $quarters = [$quarters];
                }

                // Get min and max months from selected quarters
                $quarterMonths = [
                    'Q1' => [1, 2, 3],
                    'Q2' => [4, 5, 6],
                    'Q3' => [7, 8, 9],
                    'Q4' => [10, 11, 12]
                ];

                $allMonths = [];
                foreach ($quarters as $quarter) {
                    $allMonths = array_merge($allMonths, $quarterMonths[$quarter]);
                }

                // Sort months and get min/max
                sort($allMonths);
                return [$year, min($allMonths), $year, max($allMonths)];

            case 'semester':
                $year = $this->filterFormData['semester_year'] ?? now()->year;
                $semesters = $this->filterFormData['semesters'] ?? ['S1'];

                // Ensure semesters is always an array
                if (!is_array($semesters)) {
                    $semesters = [$semesters];
                }

                $semesterMonths = [
                    'S1' => [1, 2, 3, 4, 5, 6],
                    'S2' => [7, 8, 9, 10, 11, 12]
                ];

                $allMonths = [];
                foreach ($semesters as $semester) {
                    $allMonths = array_merge($allMonths, $semesterMonths[$semester]);
                }

                // Sort months and get min/max
                sort($allMonths);
                return [$year, min($allMonths), $year, max($allMonths)];

            case 'yearly':
                $years = $this->filterFormData['yearly_years'] ?? [now()->year];

                // Ensure years is always an array
                if (!is_array($years)) {
                    $years = [$years];
                }

                return [min($years), 1, max($years), 12];

            default: // custom
                return [
                    $this->filterFormData['start_year'] ?? now()->year,
                    $this->filterFormData['start_month'] ?? 1,
                    $this->filterFormData['end_year'] ?? now()->year,
                    $this->filterFormData['end_month'] ?? now()->month
                ];
        }
    }

    /**
     * Return available months for a given report year (limits options to months that exist in laporan_imuts).
     * Falls back to full month list when no records exist for the year.
     *
     * @param mixed $year
     * @return array
     */
    protected function getMonthsForYearOptions($year): array
    {
        $year = $year ? (int) $year : (int) (LaporanImut::max('report_year') ?? now()->year);

        $monthRows = LaporanImut::where('report_year', $year)
            ->distinct()
            ->orderBy('report_month')
            ->pluck('report_month')
            ->toArray();

        $labels = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des'
        ];

        if (empty($monthRows)) {
            return $labels; // fallback to full list
        }

        $result = [];
        foreach ($monthRows as $m) {
            $result[(int) $m] = $labels[(int) $m] ?? (string) $m;
        }

        return $result;
    }

    /**
     * Return latest report year available in laporan_imuts or null.
     */
    protected function getLatestReportYear(): ?int
    {
        return LaporanImut::max('report_year') ? (int) LaporanImut::max('report_year') : null;
    }

    /**
     * Return latest report month for a given year (or null if none)
     */
    protected function getLatestReportMonthForYear(int $year): ?int
    {
        return LaporanImut::where('report_year', $year)->max('report_month') ? (int) LaporanImut::where('report_year', $year)->max('report_month') : null;
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
