<?php

namespace App\Observers;

use App\Models\LaporanImut;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Observer untuk LaporanImut
 * Menghapus cache terkait ketika terjadi perubahan
 */
class LaporanImutObserver
{
    /**
     * Handle the LaporanImut "created" event.
     */
    public function created(LaporanImut $laporanImut): void
    {
        $this->clearCache($laporanImut);
        Log::info("✅ LaporanImut created: ID {$laporanImut->id} - Cache cleared");
    }

    /**
     * Handle the LaporanImut "updated" event.
     */
    public function updated(LaporanImut $laporanImut): void
    {
        $this->clearCache($laporanImut);
        Log::info("✏️ LaporanImut updated: ID {$laporanImut->id} - Cache cleared");
    }

    /**
     * Handle the LaporanImut "deleted" event.
     */
    public function deleted(LaporanImut $laporanImut): void
    {
        $this->clearCache($laporanImut);
        Log::warning("⚠️ LaporanImut deleted: ID {$laporanImut->id} - Cache cleared");
    }

    /**
     * Handle the LaporanImut "restored" event.
     */
    public function restored(LaporanImut $laporanImut): void
    {
        $this->clearCache($laporanImut);
        Log::notice("🔄 LaporanImut restored: ID {$laporanImut->id} - Cache cleared");
    }

    /**
     * Clear all related cache for this laporan
     */
    protected function clearCache(LaporanImut $laporanImut): void
    {
        // Clear cache untuk unit kerja completion stats
        Cache::forget(CacheKey::unitKerjaCompletionStats($laporanImut->id));

        // Clear cache untuk imut data completion stats
        Cache::forget(CacheKey::imutDataCompletionStats($laporanImut->id));

        // Clear cache untuk chart series data
        Cache::forget(CacheKey::imutChartSeriesData($laporanImut->id));

        // Clear cache untuk dashboard data
        Cache::forget(CacheKey::dashboardSiimutAllData($laporanImut->id));

        // Clear cache untuk penilaian grouped by profile
        Cache::forget(CacheKey::penilaianGroupedByProfile($laporanImut->id));

        // Clear latest laporan cache jika ini adalah laporan terbaru
        Cache::forget(CacheKey::latestLaporan());
    }
}
