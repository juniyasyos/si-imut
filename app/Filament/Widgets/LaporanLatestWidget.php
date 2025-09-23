<?php

namespace App\Filament\Widgets;

use App\Services\Filament\Widgets\LaporanWidgetService;
use Filament\Widgets\Widget;

class LaporanLatestWidget extends Widget
{
    protected static string $view = 'filament.widgets.laporan-latest-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return app(LaporanWidgetService::class)->canViewLaporan();
    }

    public function getLaporan()
    {
        return app(LaporanWidgetService::class)->getLatestLaporan();
    }

    public function getLaporanData(): array
    {
        return app(LaporanWidgetService::class)->getLaporanWidgetData();
    }
}
