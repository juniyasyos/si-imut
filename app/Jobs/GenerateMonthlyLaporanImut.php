<?php

namespace App\Jobs;

use App\Models\LaporanImutAutoGenerationSetting;
use App\Services\LaporanImutAutoGenerationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyLaporanImut implements ShouldQueue
{
    use Queueable;

    public ?Carbon $targetDate;

    /**
     * Create a new job instance.
     */
    public function __construct(?Carbon $targetDate = null)
    {
        $this->targetDate = $targetDate ?? Carbon::now();
    }

    /**
     * Execute the job.
     */
    public function handle(LaporanImutAutoGenerationService $service): void
    {
        $settings = LaporanImutAutoGenerationSetting::getInstance();

        if (!$settings->isActive()) {
            Log::info('Auto generation skipped - feature is disabled');
            return;
        }

        try {
            $laporan = $service->generateForMonth($this->targetDate, $settings);

            if ($laporan) {
                Log::info('Monthly laporan generated successfully', [
                    'laporan_id' => $laporan->id,
                    'name' => $laporan->name,
                ]);
            } else {
                Log::info('Monthly laporan generation skipped (may already exist)', [
                    'target_date' => $this->targetDate->format('Y-m-d'),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate monthly laporan', [
                'target_date' => $this->targetDate->format('Y-m-d'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
