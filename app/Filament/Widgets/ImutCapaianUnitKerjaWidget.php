<?php

namespace App\Filament\Widgets;

use App\Domains\Imut\Models\ImutCategory;
use App\Domains\Reporting\Models\LaporanImut;
use App\Services\Chart\UnitKerjaChartDataService;
use App\Services\Data\DateFormattingService;
use App\Domains\Imut\Services\ImutChartSeriesService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianUnitKerjaWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianUnitKerjaWidget';
    protected static ?int $sort = 20;
    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;
    protected int|string|array $columnSpan = 'full';

    public function getUnitKerjaChartService(): UnitKerjaChartDataService
    {
        return app(UnitKerjaChartDataService::class);
    }

    public function getDateFormattingService(): DateFormattingService
    {
        return app(DateFormattingService::class);
    }

    protected function getChartService(): ImutChartSeriesService
    {
        return new ImutChartSeriesService();
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user
            && $user->can('widget_ImutCapaianUnitKerjaWidget')
            && $user->unitKerjas()->exists();
    }

    protected function getHeading(): ?string
    {
        return $this->getUnitKerjaChartService()->generateUnitKerjaHeading();
    }

    protected function getFormSchema(): array
    {
        $categories = $this->getChartService()->getCategories();
        $colors = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
            '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
        ];

        return [
            Section::make('Konfigurasi Series')
                ->schema(
                    [
                        Checkbox::make('show_dataLabels')
                            ->label('Tampilkan Nilai')
                            ->default(false)
                            ->reactive(),
                        ...collect($categories)->values()->map(function ($shortName, $i) use ($colors) {
                            return Fieldset::make($shortName)
                                ->schema([
                                    Grid::make()
                                        ->schema([
                                            Select::make("series_types.{$shortName}")
                                                ->label('Tipe')
                                                ->options([
                                                    'column' => 'Column',
                                                    'line'   => 'Line',
                                                ])
                                                ->default('column')
                                                ->reactive(),
                                            ColorPicker::make("series_colors.{$shortName}")
                                                ->label('Warna')
                                                ->default($colors[$i % count($colors)])
                                                ->reactive(),
                                        ])->columns(2)
                                ]);
                        })->toArray()
                    ]
                )
                ->columns(1),
        ];
    }

    protected function getOptions(): array
    {
        $cacheKey = 'imut_capaian_unit_kerja_' . md5(serialize($this->filters));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            // Get categories once and pass to service
            $categories = ImutCategory::all();
            $laporans = $this->chartDataService->getCachedLaporans();
            $chartSeries = $this->chartDataService->buildUnitKerjaChartSeries($laporans, $this->filters, $categories);

            return [
                'chart' => [
                    'type' => 'line',
                    'height' => 400,
                    'toolbar' => [
                        'show' => false,
                    ],
                ],
                'series' => $chartSeries,
                'xaxis' => [
                    'categories' => $this->dateFormattingService->generateTimeLabels(),
                ],
                'yaxis' => [
                    'title' => [
                        'text' => 'Capaian (%)'
                    ],
                    'min' => 0,
                    'max' => 100,
                ],
                'dataLabels' => [
                    'enabled' => false,
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 3,
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function (value) { return value.toFixed(1) + "%"; }',
                    ],
                ],
            ];
        });
    }

    protected function getCachedLaporans()
    {
        $unitKerjaIds = Auth::user()->unitKerjas->pluck('id')->toArray();

        return Cache::remember(
            CacheKey::imutLaporansForUnitKerjas($unitKerjaIds),
            now()->addDay(1),
            fn() => LaporanImut::with([
                'laporanUnitKerjas' => function ($query) use ($unitKerjaIds) {
                    $query->whereIn('unit_kerja_id', $unitKerjaIds);
                },
                'laporanUnitKerjas.imutPenilaians.profile.imutData.categories',
            ])
                ->whereHas('laporanUnitKerjas', function ($query) use ($unitKerjaIds) {
                    $query->whereIn('unit_kerja_id', $unitKerjaIds);
                })
                ->where('assessment_period_start', '>=', now()->subMonths(6))
                ->whereIn('status', [
                    LaporanImut::STATUS_COMPLETE,
                    LaporanImut::STATUS_COMINGSOON
                ])
                ->orderBy('assessment_period_start')
                ->get()
        );
    }
}
