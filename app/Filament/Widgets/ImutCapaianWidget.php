<?php

namespace App\Filament\Widgets;

use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Services\Filament\Widgets\ImutCapaianAllUnitWidgetService;
use App\Services\ImutChartSeriesService;
use App\Support\ApexChartConfig;
use App\Support\CacheKey;
use Carbon\Carbon;
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

class ImutCapaianWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianWidget';
    protected static ?string $heading = 'Capaian IMUT setiap Kategori Semua Unit Kerja';
    protected static ?int $sort = 4;
    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;
    protected int|string|array $columnSpan = 'full';

    public function __construct(
        private ImutCapaianAllUnitWidgetService $widgetService
    ) {
        parent::__construct();
    }

    public static function canView(): bool
    {
        return app(ImutCapaianAllUnitWidgetService::class)->canView();
    }

    protected function getFormSchema(): array
    {
        $categories = $this->widgetService->getCategories();
        $colors = $this->widgetService->getDefaultColors();

        return [
            Section::make('Konfigurasi Series')
                ->schema([
                    Checkbox::make('show_dataLabels')
                        ->label('Tampilkan Nilai')
                        ->default(false)
                        ->reactive(),
                    ...collect($categories)->values()->map(function ($shortName, $i) use ($colors) {
                        return Fieldset::make((string) $shortName)
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
                                    ])
                                    ->columns(2),
                            ]);
                    })->toArray(),
                ])
                ->columns(1)
        ];
    }

    protected function getOptions(): array
    {
        return $this->widgetService->getChartOptions($this->filterFormData ?? []);
    }
}
