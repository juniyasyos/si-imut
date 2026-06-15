<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use App\Services\Chart\ChartDataProcessorService;
use App\Services\Chart\ImutChartSeriesService;
use App\Services\Core\ImutCalculationService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';

    protected static ?string $heading = 'Capaian IMUT Per Kategori (Persentase) Berdasarkan Periode';

    protected static ?string $description = 'Grafik ini memperlihatkan persentase indikator IMUT yang berhasil memenuhi target untuk setiap kategori setiap bulan pada periode terpilih.';

    protected static ?int $sort = 5;

    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;

    protected int|string|array $columnSpan = 'full';

    public ?array $statistikData = null;

    public ?string $selectedPeriodType = 'semester';

    public ?string $selectedPeriod = null;

    public ?array $selectedCategories = null;

    public function updatedSelectedPeriodType(): void
    {
        $this->selectedPeriod = null;
        $this->dispatch('$refresh');
    }

    public function updatedSelectedPeriod(): void
    {
        $this->dispatch('$refresh');
    }

    public function getChartProcessor(): ChartDataProcessorService
    {
        return app(ChartDataProcessorService::class);
    }

    protected function getChartService(): ImutChartSeriesService
    {
        return new ImutChartSeriesService();
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutCapaianWidget');
    }

    protected function getFormSchema(): array
    {
        $selectedPeriodType = $this->filterFormData['selectedPeriodType']
            ?? $this->selectedPeriodType
            ?? 'semester';

        $periodOptions = $this->getPeriodOptions($selectedPeriodType);
        $defaultPeriod = count($periodOptions) > 0 ? array_key_first($periodOptions) : null;

        return [
            Section::make('Filter Data')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Grid::make(4)
                                ->columns(2)
                                ->schema([
                                    Select::make('selectedPeriodType')
                                        ->label('Jenis Periode')
                                        ->options([
                                            'quarter' => 'Triwulan',
                                            'semester' => 'Semester',
                                            'year' => 'Tahun',
                                        ])
                                        ->default('semester')
                                        ->required()
                                        ->reactive(),

                                    Select::make('selectedPeriod')
                                        ->label('Periode')
                                        ->options(fn(callable $get) => $this->getPeriodOptions($get('selectedPeriodType') ?? 'semester'))
                                        ->searchable()
                                        ->required()
                                        ->default($defaultPeriod)
                                        ->reactive(),

                                    Select::make('categories')
                                        ->label('Kategori Indikator')
                                        ->multiple()
                                        ->options(function () {
                                            $categories = $this->getChartService()->getCategories();
                                            return array_combine($categories, $categories);
                                        })
                                        ->default(function () {
                                            $categories = $this->getChartService()->getCategories();
                                            return array_slice($categories, 0, 5);
                                        })
                                        ->searchable()
                                        ->columnSpanFull()
                                        ->reactive(),

                                    Checkbox::make('show_dataLabels')
                                        ->label('Tampilkan Nilai di Chart')
                                        ->default(false)
                                        ->columnSpanFull()
                                        ->reactive(),
                                ])
                        ]),
                ])
                ->collapsible(),
        ];
    }

    public function getOptions(): array
    {
        $allCategories = $this->getChartService()->getCategories();

        if ($this->selectedCategories === null) {
            $this->selectedCategories = array_slice($allCategories, 0, 5);
        }

        if (array_key_exists('categories', $this->filterFormData ?? [])) {
            $this->selectedCategories = $this->filterFormData['categories'] ?? [];
        }

        $selectedCategories = $this->selectedCategories;

        $showDataLabels = $this->filterFormData['show_dataLabels'] ?? false;

        $selectedPeriodType = $this->filterFormData['selectedPeriodType']
            ?? $this->selectedPeriodType
            ?? 'semester';

        $periodOptions = $this->getPeriodOptions($selectedPeriodType);
        $defaultPeriod = count($periodOptions) > 0 ? array_key_first($periodOptions) : null;

        $selectedPeriod = $this->filterFormData['selectedPeriod']
            ?? $this->selectedPeriod
            ?? $defaultPeriod;

        $this->selectedPeriodType = $selectedPeriodType;
        $this->selectedPeriod = $selectedPeriod;

        if (!$selectedPeriod) {
            return ApexChartConfig::noDataOptions();
        }

        $periodMeta = $this->parseSelectedPeriod($selectedPeriodType, $selectedPeriod);

        $year = $periodMeta['year'];
        $monthsInPeriod = $periodMeta['months'];
        $periodLabel = $periodMeta['label'];

        $laporans = $this->getCachedLaporans()->filter(function ($laporan) use ($year, $monthsInPeriod) {
            $lYear = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->year
                : $laporan->report_year;

            $lMonth = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->month
                : $laporan->report_month;

            return $lYear == $year && in_array((int) $lMonth, $monthsInPeriod, true);
        });

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        $categories = collect($allCategories)
            ->filter(fn($name) => in_array($name, $selectedCategories, true))
            ->values()
            ->toArray();

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
            12 => 'Desember',
        ];

        $xLabels = [];

        foreach ($monthsInPeriod as $month) {
            $xLabels[] = $monthNames[$month];
        }

        $chartData = [];

        foreach ($categories as $category) {
            $chartData[$category] = [];

            foreach ($monthsInPeriod as $month) {
                $chartData[$category][$month] = 0;
            }
        }

        $laporansByMonth = [];

        foreach ($monthsInPeriod as $month) {
            $laporansByMonth[$month] = [];
        }

        foreach ($laporans as $laporan) {
            $month = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->month
                : $laporan->report_month;

            $month = (int) $month;

            if (isset($laporansByMonth[$month])) {
                $laporansByMonth[$month][] = $laporan;
            }
        }

        $calculator = app(\App\Services\Core\ImutCalculatorService::class);
        $periodStats = $this->initializePeriodStats($categories, $periodLabel);

        foreach ($laporansByMonth as $month => $monthlyLaporans) {
            $categoryData = array_fill_keys($categories, [
                'total' => 0,
                'achieved' => 0,
            ]);

            foreach ($monthlyLaporans as $laporan) {
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

                        $evaluation = $calculator->evaluatePenilaian(
                            $penilaian->numerator_value ?? 0,
                            $penilaian->denominator_value ?? 0,
                            $profile->target_value ?? 0,
                            $profile->target_operator ?? '>='
                        );

                        $categoryData[$shortName]['total'] += 1;
                        $categoryData[$shortName]['achieved'] += $evaluation['is_achieved'] ? 1 : 0;

                        $periodStats['categories_detail'][$shortName]['total_imut'] += 1;

                        if ($evaluation['is_achieved']) {
                            $periodStats['categories_detail'][$shortName]['imut_meeting_standard'] += 1;
                        } else {
                            $periodStats['categories_detail'][$shortName]['imut_below_standard'] += 1;
                        }
                    }
                }
            }

            foreach ($categories as $shortName) {
                $total = $categoryData[$shortName]['total'];
                $achieved = $categoryData[$shortName]['achieved'];

                $chartData[$shortName][$month] = $total > 0
                    ? round(($achieved / $total) * 100, 2)
                    : 0;
            }
        }

        $this->statistikData = $this->finalizePeriodStats($periodStats);

        $colors = $this->getChartService()->getDefaultColors();

        $series = [];
        $colorIndex = 0;

        foreach ($categories as $category) {
            $series[] = [
                'name' => $category,
                'type' => 'line',
                'data' => array_values($chartData[$category]),
                'color' => $colors[$colorIndex % count($colors)],
            ];

            $colorIndex++;
        }

        $options = ApexChartConfig::defaultOptions(
            $series,
            $xLabels,
            xLabelTitle: 'Bulan',
            yLabelTitle: 'Persentase Capaian (%)',
            yAxisMin: 0,
            yAxisMax: 100,
            showDataLabels: $showDataLabels,
            chartType: 'line'
        );

        $options['stroke'] = [
            'curve' => 'monotoneCubic',
            'width' => 2,
        ];

        $options['markers'] = [
            'size' => 4,
        ];

        return $options;
    }

    protected function initializePeriodStats(array $categories, string $periodLabel): array
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

    protected function finalizePeriodStats(array $stats): array
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

    protected function checkIfMeetsStandard(float $achievement, float $standard, string $operator): bool
    {
        return ImutCalculationService::meetsStandard($achievement, $standard, $operator);
    }

    public function getFooter(): ?string
    {
        if (!$this->statistikData) {
            return null;
        }

        return view('filament.widgets.imut-capaian-footer', [
            'stats' => $this->statistikData,
        ])->render();
    }

    protected function getPeriodOptions(string $periodType): array
    {
        $laporans = $this->getCachedLaporans();

        $periods = [];

        foreach ($laporans as $laporan) {
            $year = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->year
                : $laporan->report_year;

            $month = $laporan->assessment_period_start
                ? $laporan->assessment_period_start->month
                : $laporan->report_month;

            if (!$year || !$month) {
                continue;
            }

            $key = null;
            $label = null;

            if ($periodType === 'quarter') {
                $quarter = (int) ceil($month / 3);

                $key = "{$year}-Q{$quarter}";
                $label = "Triwulan {$quarter} {$year}";
            }

            if ($periodType === 'semester') {
                $semester = (int) ($month <= 6 ? 1 : 2);

                $key = "{$year}-S{$semester}";
                $label = "Semester {$semester} {$year}";
            }

            if ($periodType === 'year') {
                $key = (string) $year;
                $label = "Tahun {$year}";
            }

            if ($key && $label) {
                $periods[$key] = $label;
            }
        }

        krsort($periods);

        return $periods;
    }

    protected function parseSelectedPeriod(string $periodType, string $selectedPeriod): array
    {
        if ($periodType === 'quarter') {
            [$year, $quarterRaw] = explode('-Q', $selectedPeriod);

            $year = (int) $year;
            $quarter = (int) $quarterRaw;

            $months = [
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
            ][$quarter] ?? [1, 2, 3];

            return [
                'year' => $year,
                'months' => $months,
                'label' => "Triwulan {$quarter} {$year}",
            ];
        }

        if ($periodType === 'semester') {
            [$year, $semesterRaw] = explode('-S', $selectedPeriod);

            $year = (int) $year;
            $semester = (int) $semesterRaw;

            $months = $semester === 1
                ? [1, 2, 3, 4, 5, 6]
                : [7, 8, 9, 10, 11, 12];

            return [
                'year' => $year,
                'months' => $months,
                'label' => "Semester {$semester} {$year}",
            ];
        }

        $year = (int) $selectedPeriod;

        return [
            'year' => $year,
            'months' => range(1, 12),
            'label' => "Tahun {$year}",
        ];
    }

    protected function getCachedLaporans()
    {
        $statuses = $this->filterFormData['status'] ?? [LaporanImut::STATUS_COMPLETE];

        $cacheKey = CacheKey::imutLaporans() . '_' . implode('_', $statuses);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($statuses) {
                return LaporanImut::with([
                    'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
                ])
                    ->whereIn('status', $statuses)
                    ->orderBy('assessment_period_start', 'desc')
                    ->get();
            }
        );
    }
}