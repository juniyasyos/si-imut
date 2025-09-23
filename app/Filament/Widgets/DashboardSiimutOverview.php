<?php

namespace App\Filament\Widgets;

use App\Services\Filament\Widgets\DashboardWidgetService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardSiimutOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return app(DashboardWidgetService::class)->canViewDashboard();
    }

    protected function getStats(): array
    {
        $service = app(DashboardWidgetService::class);
        $statsData = $service->getDashboardStats();

        return collect($statsData)->map(function ($stat) {
            return Stat::make($stat['label'], $stat['value'])
                ->icon($stat['icon'] ?? null)
                ->description($stat['description'])
                ->descriptionIcon($stat['descriptionIcon'] ?? null)
                ->chart($stat['chart'] ?? [])
                ->color($stat['color']);
        })->toArray();
    }
}
