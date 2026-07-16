<?php

namespace App\Observers;

use App\Models\ImutData;
use Illuminate\Support\Facades\Log;

class ImutDataObserver
{
    /**
     * Handle the ImutData "created" event.
     */
    public function created(ImutData $imutData): void
    {
        // FormTemplate creation moved to CompleteFormTemplateSeeder for production safety
        Log::info("✅ ImutData created: ID {$imutData->id}");
    }

    /**
     * Handle the ImutData "saved" event (created or updated).
     */
    public function saved(ImutData $imutData): void
    {
        if ($imutData->status) {
            \App\Jobs\SyncOngoingLaporanPenilaian::dispatch();
        }
    }
}
