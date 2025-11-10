<?php

namespace App\Observers;

use App\Models\ImutPenilaian;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk ImutPenilaian
 * Menghapus cache terkait ketika terjadi perubahan penilaian
 */
class ImutPenilaianObserver
{
    /**
     * Handle the ImutPenilaian "created" event.
     */
    public function created(ImutPenilaian $penilaian): void
    {
        $this->clearRelatedCache($penilaian);
        Log::info("✅ ImutPenilaian created: ID {$penilaian->id} - Cache cleared");
    }

    /**
     * Handle the ImutPenilaian "updated" event.
     */
    public function updated(ImutPenilaian $penilaian): void
    {
        $this->clearRelatedCache($penilaian);
        Log::info("✏️ ImutPenilaian updated: ID {$penilaian->id} - Cache cleared");
    }

    /**
     * Handle the ImutPenilaian "deleted" event.
     */
    public function deleted(ImutPenilaian $penilaian): void
    {
        $this->clearRelatedCache($penilaian);
        Log::warning("⚠️ ImutPenilaian deleted: ID {$penilaian->id} - Cache cleared");
    }

    /**
     * Handle the ImutPenilaian "restored" event.
     */
    public function restored(ImutPenilaian $penilaian): void
    {
        $this->clearRelatedCache($penilaian);
        Log::notice("🔄 ImutPenilaian restored: ID {$penilaian->id} - Cache cleared");
    }

    /**
     * Clear all related cache for this penilaian
     */
    protected function clearRelatedCache(ImutPenilaian $penilaian): void
    {
        // Load relasi jika belum di-load
        $penilaian->loadMissing(['laporanUnitKerja.laporanImut', 'imutProfil']);

        if (!$penilaian->laporanUnitKerja?->laporanImut) {
            return;
        }

        $laporanId = $penilaian->laporanUnitKerja->laporanImut->id;

        // Clear cache untuk completion stats
        Cache::forget(CacheKey::unitKerjaCompletionStats($laporanId));
        Cache::forget(CacheKey::imutDataCompletionStats($laporanId));

        // Clear cache untuk chart data
        Cache::forget(CacheKey::imutChartSeriesData($laporanId));

        // Clear cache untuk dashboard
        Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));

        // Clear cache untuk penilaian grouped
        Cache::forget(CacheKey::penilaianGroupedByProfile($laporanId));

        // Clear cache untuk penilaian stats
        Cache::forget(CacheKey::getPenilaianStats($laporanId, true));
        Cache::forget(CacheKey::getPenilaianStats($laporanId, false));

        // Clear latest laporan cache
        Cache::forget(CacheKey::latestLaporan());
    }
}
