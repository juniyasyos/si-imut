<?php

namespace App\Filament\Widgets\UnitKerja;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UnitKerjaInfo extends Widget
{
    protected string $view = 'filament.widgets.unit-kerja-info';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->can('widget_UnitKerjaInfo')) {
            return $user->hasUnitKerjaCached();
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
