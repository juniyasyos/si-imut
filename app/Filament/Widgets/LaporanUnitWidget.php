<?php

namespace App\Filament\Widgets;

use App\Models\DailyReportResponse;
use App\Models\LaporanImut;
use App\Filament\Widgets\LaporanLatestWidget;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\DailyReport\WidgetDataService;

class LaporanUnitWidget extends Widget
{
    protected static string $view = 'filament.widgets.laporan-unit-widget';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // user must have the new permission and must belong to at least one unit kerja
        return $user->hasUnitKerjaCached();
    }

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getLaporan(): ?LaporanImut
    {
        return LaporanLatestWidget::getLatestLaporan();
    }

    /**
     * Summaries per unit kerja that belong to current user and are part of latest laporan
     *
     * @return array<int,array{
     *     unit_id:int,
     *     unit_name:string,
     *     today:int,
     *     perfect:int,
     *     last_submission:?\Illuminate\Support\Carbon
     * }>
     */
    public function getUnitSummaries(): array
    {
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return [];
        }

        $service = app(WidgetDataService::class);
        return $service->getUnitSummariesForLaporan($laporan);
    }
}
