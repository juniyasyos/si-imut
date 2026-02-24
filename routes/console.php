<?php

use App\Console\Commands\GenerateMonthlyLaporanImut;
use App\Console\Commands\NotifikasiDeadlineLaporan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Daily notification H-2 and H-1 before deadline
Schedule::command(NotifikasiDeadlineLaporan::class)->dailyAt('08:00');

// Monthly report generation on 5th of each month at 01:00
// Automatically calculates N/D from previous month's daily reports
Schedule::command(GenerateMonthlyLaporanImut::class, ['--auto-calculate'])
    ->monthlyOn(1, '01:00')
    ->description('Auto-generate monthly IMUT report with daily report calculation');

// Periodic sync of local/public files to S3 fallback
Schedule::command('storage:sync-local-to-s3')->dailyAt('02:00')
    ->description('Sync local/public disks to S3 (via queue)');
