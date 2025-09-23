<?php

namespace App\Filament\Widgets;

use App\Services\Filament\Widgets\ImutCapaianWidgetService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ImutCapaianUnitKerjaWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'imutCapaianUnitKerjaWidget';
    protected static ?int $sort = 20;
    protected static MaxWidth|string $filterFormWidth = MaxWidth::ExtraLarge;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return app(ImutCapaianWidgetService::class)->canViewCapaian();
    }

    protected function getHeading(): ?string
    {
        return app(ImutCapaianWidgetService::class)->getWidgetHeading();
    }

    protected function getFormSchema(): array
    {
        $service = app(ImutCapaianWidgetService::class);
        $data = $service->getFormSchemaData();

        $categories = $data['categories'];
        $colors = $data['colors'];

        return [
            Section::make('Konfigurasi Series')
                ->schema([
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
                ])
                ->columns(1),
        ];
    }

    protected function getOptions(): array
    {
        $service = app(ImutCapaianWidgetService::class);
        return $service->getChartOptions($this->filterFormData ?? []);
    }
}
