<?php

namespace App\Observers;

use App\Models\ImutProfile;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImutProfileObserver
{
    /**
     * Handle the ImutProfile "creating" event.
     */
    public function creating(ImutProfile $profile): void
    {
        Log::info('Creating new ImutProfile', [
            'version' => $profile->version,
            'imut_data_id' => $profile->imut_data_id,
        ]);
    }

    /**
     * Handle the ImutProfile "created" event.
     */
    public function created(ImutProfile $profile): void
    {
        // Clear related cache
        $this->clearRelatedCache();

        // Create initial benchmarking if needed
        $this->createInitialBenchmarking($profile);

        Log::info('ImutProfile created successfully', [
            'id' => $profile->id,
            'version' => $profile->version,
            'imut_data_id' => $profile->imut_data_id,
        ]);
    }

    /**
     * Handle the ImutProfile "updated" event.
     */
    public function updated(ImutProfile $profile): void
    {
        // Clear related cache
        $this->clearRelatedCache();

        // If version changed, handle version update logic
        if ($profile->wasChanged('version')) {
            $this->handleVersionUpdate($profile);
        }

        Log::info('ImutProfile updated', [
            'id' => $profile->id,
            'changes' => $profile->getChanges(),
        ]);
    }

    /**
     * Handle the ImutProfile "deleted" event.
     */
    public function deleted(ImutProfile $profile): void
    {
        // Clear related cache
        $this->clearRelatedCache();

        Log::info('ImutProfile deleted', [
            'id' => $profile->id,
            'version' => $profile->version,
        ]);
    }

    /**
     * Handle the ImutProfile "restored" event.
     */
    public function restored(ImutProfile $profile): void
    {
        // Clear cache when profile is restored
        $this->clearRelatedCache();

        Log::info('ImutProfile restored', [
            'id' => $profile->id,
            'version' => $profile->version,
        ]);
    }

    /**
     * Handle the ImutProfile "force deleted" event.
     */
    public function forceDeleted(ImutProfile $profile): void
    {
        // Clear related cache
        $this->clearRelatedCache();

        Log::warning('ImutProfile force deleted', [
            'id' => $profile->id,
            'version' => $profile->version,
        ]);
    }

    /**
     * Clear related cache keys
     */
    private function clearRelatedCache(): void
    {
        // Clear profile-related caches
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData());

        // Clear ImutData related caches
        Cache::forget(CacheKey::imutLaporans());
    }

    /**
     * Create initial benchmarking data
     */
    private function createInitialBenchmarking(ImutProfile $profile): void
    {
        // This could create default regional benchmarking
        // Based on business requirements

        Log::info('Creating initial benchmarking for profile', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * Handle version update logic
     */
    private function handleVersionUpdate(ImutProfile $profile): void
    {
        // Archive previous version or handle version control logic
        Log::info('Profile version updated', [
            'profile_id' => $profile->id,
            'old_version' => $profile->getOriginal('version'),
            'new_version' => $profile->version,
        ]);
    }
}
