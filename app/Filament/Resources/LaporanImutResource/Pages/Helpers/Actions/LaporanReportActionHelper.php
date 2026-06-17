<?php

namespace App\Filament\Resources\LaporanImutResource\Pages\Helpers\Actions;

use App\Models\UnitKerja;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LaporanReportActionHelper
{
    public static function buildCategoryRedirect(array $data)
    {
        $categories = implode(',', $data['imut_category'] ?? []);
        $periode = self::resolvePeriode($data);

        $url = route('laporan.indikator-mutu.by-category');
        $url .= '?categories=' . urlencode($categories) . '&periode=' . urlencode($periode);

        return redirect($url);
    }

    public static function buildUnitKerjaRedirect(array $data): ?RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasAnyRole(['super_admin', 'admin', 'tim_mutu'])) {
            $hasAccess = $user->unitKerjas()->where('unit_kerja_id', $data['unit_kerja_id'])->exists();

            if (! $hasAccess) {
                Notification::make()
                    ->title('Akses Ditolak')
                    ->body('Anda tidak memiliki akses ke unit kerja yang dipilih.')
                    ->danger()
                    ->send();

                return null;
            }
        }

        $unitKerja = UnitKerja::find($data['unit_kerja_id']);

        if (! $unitKerja) {
            Notification::make()
                ->title('Unit Kerja tidak ditemukan')
                ->danger()
                ->send();

            return null;
        }

        $tipe = $data['periode_tipe'];
        $periode = self::resolvePeriode($data);

        $url = route('laporan.indikator-mutu.unit-kerja.show-with-period', [
            'unitKerja' => $unitKerja->slug,
            'tipe' => $tipe,
            'periode' => $periode,
        ]);

        return redirect($url);
    }

    private static function resolvePeriode(array $data): string
    {
        $tipe = $data['periode_tipe'] ?? 'yearly';

        if ($tipe === 'yearly') {
            return (string) ($data['periode_tahun'] ?? now()->year);
        }

        if ($tipe === 'quarterly') {
            return ($data['periode_tahun'] ?? now()->year) . '-' . ($data['periode_quarter'] ?? 'Q1');
        }

        if ($tipe === 'semester') {
            return ($data['periode_tahun'] ?? now()->year) . '-' . ($data['periode_semester'] ?? 'S1');
        }

        return ($data['periode_start_year'] ?? now()->year) . '-' . ($data['periode_start_month'] ?? '01')
            . ',' . ($data['periode_end_year'] ?? now()->year) . '-' . ($data['periode_end_month'] ?? '12');
    }
}
