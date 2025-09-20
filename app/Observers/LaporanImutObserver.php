<?php

namespace App\Observers;

use App\Models\LaporanImut;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LaporanImutObserver
{
    /**
     * Handle the LaporanImut "creating" event.
     */
    public function creating(LaporanImut $laporanImut): void
    {
        // Set default values if not provided
        if (empty($laporanImut->status)) {
            $laporanImut->status = LaporanImut::STATUS_PROCESS;
        }

        Log::info('Creating new LaporanImut', [
            'name' => $laporanImut->name,
            'status' => $laporanImut->status,
        ]);
    }

    /**
     * Handle the LaporanImut "created" event.
     */
    public function created(LaporanImut $laporanImut): void
    {
        // Clear cache when new laporan is created
        $this->clearRelatedCache();

        // Create LaporanUnitKerja for all active units
        $this->createUnitKerjaRelationships($laporanImut);

        Log::info('LaporanImut created successfully', [
            'id' => $laporanImut->id,
            'name' => $laporanImut->name,
        ]);
    }

    /**
     * Handle the LaporanImut "updated" event.
     */
    public function updated(LaporanImut $laporanImut): void
    {
        // Clear cache when laporan is updated
        $this->clearRelatedCache($laporanImut->id);

        // If status changed to complete, trigger completion logic
        if ($laporanImut->wasChanged('status') && $laporanImut->status === LaporanImut::STATUS_COMPLETE) {
            $this->handleLaporanCompletion($laporanImut);
        }

        Log::info('LaporanImut updated', [
            'id' => $laporanImut->id,
            'changes' => $laporanImut->getChanges(),
        ]);
    }

    /**
     * Handle the LaporanImut "deleted" event.
     */
    public function deleted(LaporanImut $laporanImut): void
    {
        // Clear all related cache
        $this->clearRelatedCache($laporanImut->id);

        Log::info('LaporanImut deleted', [
            'id' => $laporanImut->id,
            'name' => $laporanImut->name,
        ]);
    }

    /**
     * Handle the LaporanImut "restored" event.
     */
    public function restored(LaporanImut $laporanImut): void
    {
        // Clear cache when laporan is restored
        $this->clearRelatedCache();

        Log::info('LaporanImut restored', [
            'id' => $laporanImut->id,
            'name' => $laporanImut->name,
        ]);
    }

    /**
     * Handle the LaporanImut "force deleted" event.
     */
    public function forceDeleted(LaporanImut $laporanImut): void
    {
        // Clear all related cache
        $this->clearRelatedCache($laporanImut->id);

        Log::warning('LaporanImut force deleted', [
            'id' => $laporanImut->id,
            'name' => $laporanImut->name,
        ]);
    }

    /**
     * Clear related cache keys
     */
    private function clearRelatedCache(?int $laporanId = null): void
    {
        // Clear general laporan caches
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData());

        // Clear specific laporan cache if provided
        if ($laporanId) {
            Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
        }

        // Clear recent laporan cache (multiple limits)
        for ($limit = 1; $limit <= 10; $limit++) {
            Cache::forget(CacheKey::recentLaporanList($limit));
        }

        Cache::forget(CacheKey::imutLaporans());
    }

    /**
     * Create unit kerja relationships for laporan
     */
    private function createUnitKerjaRelationships(LaporanImut $laporanImut): void
    {
        $unitKerjas = \App\Models\UnitKerja::all();

        foreach ($unitKerjas as $unitKerja) {
            \App\Models\LaporanUnitKerja::updateOrCreate([
                'laporan_imut_id' => $laporanImut->id,
                'unit_kerja_id' => $unitKerja->id,
            ]);
        }
    }

    /**
     * Handle laporan completion logic
     */
    private function handleLaporanCompletion(LaporanImut $laporanImut): void
    {
        // Create next period laporan automatically
        $this->createNextPeriodLaporan($laporanImut);

        // Generate completion reports or notifications
        // This could trigger email notifications to stakeholders
        Log::info('LaporanImut completed, triggering post-completion actions', [
            'id' => $laporanImut->id,
        ]);
    }

    /**
     * Create next period laporan automatically
     */
    private function createNextPeriodLaporan(LaporanImut $laporanImut): void
    {
        $nextStart = $laporanImut->assessment_period_end->addDay();
        $nextEnd = $nextStart->copy()->addMonth()->subDay();

        // Check if next period laporan already exists
        $exists = LaporanImut::where('assessment_period_start', $nextStart->format('Y-m-d'))
            ->where('assessment_period_end', $nextEnd->format('Y-m-d'))
            ->exists();

        if (!$exists) {
            LaporanImut::create([
                'name' => "Laporan IMUT Periode {$nextStart->format('m/Y')}",
                'assessment_period_start' => $nextStart,
                'assessment_period_end' => $nextEnd,
                'status' => 'coming_soon',
                'created_by' => $laporanImut->created_by,
            ]);

            Log::info('Next period laporan created automatically', [
                'current_laporan_id' => $laporanImut->id,
                'next_period_start' => $nextStart->format('Y-m-d'),
            ]);
        }
    }
}
