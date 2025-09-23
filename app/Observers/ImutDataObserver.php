<?php

namespace App\Observers;

use App\Models\ImutData;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;

class ImutDataObserver
{
    public function created(ImutData $imutData): void
    {
        $this->clearRelevantCache($imutData);
    }

    public function updated(ImutData $imutData): void
    {
        $this->clearRelevantCache($imutData);
    }

    public function deleted(ImutData $imutData): void
    {
        $this->clearRelevantCache($imutData);
    }

    public function restored(ImutData $imutData): void
    {
        $this->clearRelevantCache($imutData);
    }

    private function clearRelevantCache(ImutData $imutData): void
    {
        // Clear cache yang berkaitan dengan ImutData
        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData());

        // Clear cache berdasarkan laporan unit kerja jika ada
        $laporanUnitKerja = $imutData->laporanUnitKerja;
        if ($laporanUnitKerja) {
            $laporanId = $laporanUnitKerja->laporan_imut_id;
            $unitKerjaId = $laporanUnitKerja->unit_kerja_id;

            Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));
            Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
        }
    }
}
