<?php

namespace App\Filament\Widgets;

use App\Models\LaporanImut;
use App\Services\Chart\ImutCapaianDataService;
use App\Services\Chart\ImutChartSeriesService;
use App\Services\Chart\ImutPeriodService;
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

    protected static bool $isLazy = true;

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

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutCapaianWidget');
    }

    protected function getFormSchema(): array
    {
        $selectedPeriodType = $this->filterFormData['selectedPeriodType']
            ?? $this->selectedPeriodType
            ?? 'semester';

        $periodOptions = $this->periodService()->getAvailablePeriods(
            $this->getCachedLaporans(),
            $selectedPeriodType
        );
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
                                        ->options(fn(callable $get) => $this->periodService()->getAvailablePeriods(
                                            $this->getCachedLaporans(),
                                            $get('selectedPeriodType') ?? 'semester'
                                        ))
                                        ->searchable()
                                        ->required()
                                        ->default($defaultPeriod)
                                        ->reactive(),

                                    Select::make('categories')
                                        ->label('Kategori Indikator')
                                        ->multiple()
                                        ->maxItems(2)
                                        ->options(function () {
                                            $categories = $this->chartSeriesService()->getCategories();

                                            return array_combine($categories, $categories);
                                        })
                                        ->default(function () {
                                            $categories = $this->chartSeriesService()->getCategories();

                                            return array_slice($categories, 0, 2);
                                        })
                                        ->searchable()
                                        ->columnSpanFull()
                                        ->reactive(),

                                    Checkbox::make('show_dataLabels')
                                        ->label('Tampilkan Nilai di Chart')
                                        ->default(false)
                                        ->columnSpanFull()
                                        ->reactive(),
                                ]),
                        ]),
                ])
                ->collapsible(),
        ];
    }

    public function getOptions(): array
    {
        $allCategories = $this->chartSeriesService()->getCategories();
        $selectedCategories = $this->resolveSelectedCategories($allCategories);
        $showDataLabels = $this->filterFormData['show_dataLabels'] ?? false;

        $periodType = $this->filterFormData['selectedPeriodType']
            ?? $this->selectedPeriodType ?? 'semester';
        $this->selectedPeriodType = $periodType;

        $periodOptions = $this->periodService()->getAvailablePeriods(
            $this->getCachedLaporans(),
            $periodType
        );
        $selectedPeriod = $this->filterFormData['selectedPeriod']
            ?? $this->selectedPeriod
            ?? (count($periodOptions) > 0 ? array_key_first($periodOptions) : null);
        $this->selectedPeriod = $selectedPeriod;

        if (!$selectedPeriod) {
            return ApexChartConfig::noDataOptions();
        }

        // Parse periode dan filter laporan
        $periodMeta = $this->periodService()->parsePeriod($periodType, $selectedPeriod);
        $laporans = $this->periodService()->filterByPeriod(
            $this->getCachedLaporans(),
            $periodMeta['year'],
            $periodMeta['months'],
        );

        if ($laporans->isEmpty()) {
            return ApexChartConfig::noDataOptions();
        }

        $laporans->loadMissing([
            'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
        ]);

        // Filter kategori yang dipilih
        $categories = collect($allCategories)
            ->filter(fn($name) => in_array($name, $selectedCategories, true))
            ->values()
            ->toArray();

        // Hitung data capaian via service
        $result = $this->capaianDataService()->calculatePeriodCapaian(
            $laporans,
            $categories,
            $periodMeta['months'],
            $periodMeta['label'],
        );

        $this->statistikData = $result->statistikData;

        // Build chart
        return $this->buildChartOptions(
            $categories,
            $result->chartData,
            $periodMeta['months'],
            $showDataLabels,
        );
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

    // ──────────────────────────────────────────────
    //  Private Helpers
    // ──────────────────────────────────────────────

    /**
     * Resolve kategori yang dipilih dari form state.
     */
    private function resolveSelectedCategories(array $allCategories): array
    {
        if ($this->selectedCategories === null) {
            $this->selectedCategories = array_slice($allCategories, 0, 2);
        }

        if (array_key_exists('categories', $this->filterFormData ?? [])) {
            $this->selectedCategories = $this->filterFormData['categories'] ?? [];
        }

        return $this->selectedCategories;
    }

    /**
     * Build ApexCharts options dari data capaian.
     */
    private function buildChartOptions(
        array $categories,
        array $chartData,
        array $monthsInPeriod,
        bool $showDataLabels,
    ): array {
        $colors = $this->chartSeriesService()->getDefaultColors();
        $xLabels = $this->periodService()->getMonthLabels($monthsInPeriod);

        $series = [];
        foreach ($categories as $i => $category) {
            $series[] = [
                'name' => $category,
                'type' => 'area',
                'data' => array_values($chartData[$category]),
                'color' => $colors[$i % count($colors)],
            ];
        }

        $options = ApexChartConfig::defaultOptions(
            $series,
            $xLabels,
            xLabelTitle: 'Bulan',
            yLabelTitle: 'Persentase Capaian (%)',
            yAxisMin: 0,
            yAxisMax: 100,
            showDataLabels: $showDataLabels,
            chartType: 'area',
        );

        // Area gradient fill
        $options['fill'] = [
            'type' => 'gradient',
            'gradient' => [
                'shadeIntensity' => 1,
                'opacityFrom' => 0.35,
                'opacityTo' => 0.05,
                'stops' => [0, 90, 100],
            ],
        ];

        // Smooth line styling
        $options['stroke'] = [
            'curve' => 'monotoneCubic',
            'width' => 2.5,
        ];

        $options['markers'] = [
            'size' => 4,
            'strokeWidth' => 2,
            'hover' => ['sizeOffset' => 3],
        ];

        // Annotation: target line 80%
        $options['annotations'] = [
            'yaxis' => [
                [
                    'y' => 80,
                    'borderColor' => '#ef4444',
                    'strokeDashArray' => 5,
                    'label' => [
                        'text' => 'Target Minimal 80%',
                        'position' => 'left',
                        'borderColor' => '#ef4444',
                        'style' => [
                            'color' => '#fff',
                            'background' => '#ef4444',
                            'fontSize' => '11px',
                            'padding' => [
                                'left' => 8,
                                'right' => 8,
                                'top' => 3,
                                'bottom' => 3,
                            ],
                        ],
                    ],
                ]
            ],
        ];

        // Enhanced tooltip
        $options['tooltip'] = [
            'shared' => true,
            'intersect' => false,
            'y' => [
                'formatter' => 'function(val) { return val !== null ? val.toFixed(1) + "%" : "-"; }',
            ],
        ];

        // Responsive breakpoints
        $options['responsive'] = [
            [
                'breakpoint' => 640,
                'options' => [
                    'chart' => ['height' => 320],
                    'legend' => ['position' => 'bottom', 'fontSize' => '11px'],
                    'xaxis' => ['labels' => ['rotate' => -60, 'style' => ['fontSize' => '10px']]],
                ],
            ],
        ];

        // Legend interactivity
        $options['legend'] = [
            'position' => 'top',
            'horizontalAlign' => 'center',
            'fontSize' => '13px',
            'fontWeight' => 500,
            'itemMargin' => ['horizontal' => 12, 'vertical' => 4],
            'onItemClick' => ['toggleDataSeries' => true],
        ];

        return $options;
    }

    // ──────────────────────────────────────────────
    //  Service Accessors
    // ──────────────────────────────────────────────

    private function periodService(): ImutPeriodService
    {
        return app(ImutPeriodService::class);
    }

    private function chartSeriesService(): ImutChartSeriesService
    {
        return new ImutChartSeriesService;
    }

    private function capaianDataService(): ImutCapaianDataService
    {
        return app(ImutCapaianDataService::class);
    }

    protected function getCachedLaporans()
    {
        $statuses = $this->filterFormData['status'] ?? [LaporanImut::STATUS_COMPLETE];

        $cacheKey = CacheKey::imutCapaianWidgetBasic($statuses);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($statuses) {
                return LaporanImut::whereIn('status', $statuses)
                    ->orderBy('assessment_period_start', 'desc')
                    ->get();
            }
        );
    }
}
