<?php

namespace App\Observers;

use App\Models\ImutBenchmarking;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImutBenchmarkingObserver
{
    /**
     * Handle the ImutBenchmarking "creating" event.
     */
    public function creating(ImutBenchmarking $benchmarking): void
    {
        // Auto-set created_by and updated_by
        if (Auth::check()) {
            $benchmarking->created_by = $benchmarking->created_by ?? Auth::id();
            $benchmarking->updated_by = Auth::id();
        }

        // Set default is_active if not provided
        if ($benchmarking->is_active === null) {
            $benchmarking->is_active = true;
        }

        Log::info("Creating benchmarking: {$benchmarking->imut_data_id} - {$benchmarking->year}/{$benchmarking->month}");
    }

    /**
     * Handle the ImutBenchmarking "created" event.
     */
    public function created(ImutBenchmarking $benchmarking): void
    {
        // Invalidate cache after creation
        $this->invalidateCache($benchmarking);

        Log::info("✅ Benchmarking created: ID {$benchmarking->id}");
    }

    /**
     * Handle the ImutBenchmarking "updating" event.
     */
    public function updating(ImutBenchmarking $benchmarking): void
    {
        // Auto-update updated_by
        if (Auth::check()) {
            $benchmarking->updated_by = Auth::id();
        }

        Log::info("Updating benchmarking: ID {$benchmarking->id}");
    }

    /**
     * Handle the ImutBenchmarking "updated" event.
     */
    public function updated(ImutBenchmarking $benchmarking): void
    {
        // Invalidate cache after update
        $this->invalidateCache($benchmarking);

        Log::info("✏️ Benchmarking updated: ID {$benchmarking->id}");
    }

    /**
     * Handle the ImutBenchmarking "deleted" event.
     */
    public function deleted(ImutBenchmarking $benchmarking): void
    {
        // Invalidate cache after deletion
        $this->invalidateCache($benchmarking);

        Log::warning("⚠️ Benchmarking deleted: ID {$benchmarking->id}");
    }

    /**
     * Invalidate related cache keys
     */
    protected function invalidateCache(ImutBenchmarking $benchmarking): void
    {
        CacheKey::invalidateBenchmarkingCache(
            $benchmarking->imut_data_id,
            $benchmarking->year,
            $benchmarking->region_type_id
        );

        Log::debug("Cache invalidated for benchmarking: indicator={$benchmarking->imut_data_id}, year={$benchmarking->year}, region={$benchmarking->region_type_id}");
    }
}
