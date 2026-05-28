<?php

use App\Console\Commands\GenerateMonthlyLaporanImut;
use App\Console\Commands\NotifikasiDeadlineLaporan;
use App\Models\LaporanImutAutoGenerationSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Daily notification H-2 and H-1 before deadline
Schedule::command(NotifikasiDeadlineLaporan::class)->dailyAt('08:00');

// Monthly report generation schedule is configurable from Filament action settings.
$monthlyGenerationDay = 1;
$monthlyGenerationTime = '01:00';

try {
    if (Schema::hasTable('laporan_imut_auto_generation_settings')) {
        $settings = LaporanImutAutoGenerationSetting::query()->first();

        if ($settings) {
            $monthlyGenerationDay = max(1, min(28, (int) ($settings->schedule_day_of_month ?? 1)));
            $monthlyGenerationTime = preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', (string) ($settings->schedule_run_time ?? '01:00'))
                ? (string) $settings->schedule_run_time
                : '01:00';
        }
    }
} catch (\Throwable $e) {
    // Keep fallback schedule values when DB/schema is not ready.
}

// Automatically calculates N/D from previous month's daily reports
Schedule::command(GenerateMonthlyLaporanImut::class, ['--auto-calculate'])
    ->monthlyOn($monthlyGenerationDay, $monthlyGenerationTime)
    ->description('Auto-generate monthly IMUT report with daily report calculation');

// Periodic sync of local/public files to S3 fallback
Schedule::command('storage:sync-local-to-s3')->dailyAt('02:00')
    ->description('Sync local/public disks to S3 (via queue)');
