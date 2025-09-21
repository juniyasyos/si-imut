<?php

namespace App\Services;

use App\Filament\Resources\ImutDataResource;
use App\Filament\Resources\UnitKerjaResource;

class LaporanRedirectService
{
    /**
     * Get redirect URL for IMUT Data with laporan filter
     */
    public static function getRedirectUrlForImutData(int $laporanId): string
    {
        return ImutDataResource::getUrl('index', [
            'tableFilters' => [
                'laporan_id' => [
                    'value' => $laporanId,
                ],
            ],
        ]);
    }

    /**
     * Get redirect URL for Unit Kerja with laporan filter
     */
    public static function getRedirectUrlForUnitKerja(int $laporanId): string
    {
        return UnitKerjaResource::getUrl('index', [
            'tableFilters' => [
                'laporan_id' => [
                    'value' => $laporanId,
                ],
            ],
        ]);
    }
}
