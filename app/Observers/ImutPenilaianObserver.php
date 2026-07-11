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
        $penilaian->loadMissing(['laporanUnitKerja.laporanImut', 'profile']);

        if (!$penilaian->laporanUnitKerja) {
            return;
        }

        $laporanUnitKerja = $penilaian->laporanUnitKerja;
        $unitKerjaId = $laporanUnitKerja->unit_kerja_id;

        if (!$laporanUnitKerja->laporanImut) {
            return;
        }

        $laporanImut = $laporanUnitKerja->laporanImut;
        $laporanId = $laporanImut->id;

        // Basic detail and dashboard caches
        Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));
        Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
        Cache::forget(CacheKey::dashboardSiimutChartData());
        Cache::forget(CacheKey::penilaianGroupedByProfile($laporanId));
        Cache::forget(CacheKey::imutChartSeriesData($laporanId));

        // Clear completion chart cache
        Cache::forget(CacheKey::unitKerjaCompletionStats($laporanId));
        Cache::forget(CacheKey::imutDataCompletionStats($laporanId));

        // Clear widget recommendation analysis cache
        Cache::forget(CacheKey::recommendationAnalysisTimMutuOngoing());
        Cache::forget(CacheKey::recommendationAnalysisTimMutuPrevious());
        Cache::forget(CacheKey::recommendationAnalysisCompletionStats($laporanId));
        Cache::forget(CacheKey::recommendationAnalysisCompletionStatsUnitKerja($laporanId, $unitKerjaId));

        // Clear for all users' unit kerja widgets
        $userIds = \App\Models\User::whereHas('unitKerjas', function ($q) use ($unitKerjaId) {
            $q->where('unit_kerja.id', $unitKerjaId);
        })->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            Cache::forget(CacheKey::recommendationAnalysisUnitKerjaOngoing($userId));
            Cache::forget(CacheKey::recommendationAnalysisUnitKerjaPrevious($userId));
        }

        // Invalidasi data indikator berdasarkan profil
        if ($penilaian->profile) {
            $imutDataId = $penilaian->profile->imut_data_id;
            $year = \Carbon\Carbon::parse($laporanImut->assessment_period_start)->year;

            // Invalidate semua kombinasi bulan untuk imutPenilaian dan imutPenilaianImutDataUnitKerja
            CacheKey::invalidateImutPenilaianImutDataUnitKerjaCache($imutDataId, $year, $unitKerjaId);
            CacheKey::invalidateImutPenilaianCache($imutDataId, $year);

            // Invalidate semua cache benchmarking dengan benar (menggunakan fungsi utilitas)
            $regionTypeIds = \App\Models\RegionType::query()->pluck('id');
            foreach ($regionTypeIds as $regionTypeId) {
                CacheKey::invalidateBenchmarkingCache($imutDataId, $year, $regionTypeId);
            }
        }

        Cache::forget(CacheKey::getPenilaianStats($laporanId, false));
        Cache::forget(CacheKey::getPenilaianStats($laporanId, true));

        // Laporan globals
        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
    }
}
