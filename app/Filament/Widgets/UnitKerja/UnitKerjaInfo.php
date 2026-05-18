<?php

namespace App\Filament\Widgets\UnitKerja;

use App\Support\CacheKey;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnitKerjaInfo extends Widget
{
    protected static string $view = 'filament.widgets.unit-kerja-info';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->can('widget_UnitKerjaInfo')) {
            return cache()->remember(
                CacheKey::userHasUnitKerja($user->id),
                now()->addMinutes(10),
                fn() => $user->unitKerjas()->exists()
            );
        }

        return false;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $unitKerja = $user->unitKerjas()->first();

        if (!$unitKerja) {
            return [
                'unitKerja' => null,
                'stats' => null,
            ];
        }

        // Get additional statistics
        $stats = [
            'total_imut_data' => $unitKerja->imutData()->count(),
            'total_users' => $unitKerja->users()->count(),
            'total_reports' => $unitKerja->laporanUnitKerjas()->count(),
            'completed_assessments' => $unitKerja->laporanUnitKerjas()
                ->whereHas('imutPenilaians', function ($query) {
                    $query->whereNotNull('numerator_value')
                        ->whereNotNull('denominator_value');
                })
                ->count(),
        ];

        return compact('unitKerja', 'stats');
    }
}
