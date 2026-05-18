<?php

namespace App\Jobs;

use App\Models\LaporanImut;
use App\Services\Reporting\DailyReportAggregationService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateLaporanFromDailyReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $laporanId) {}

    public function handle(): void
    {
        try {
            $laporan = LaporanImut::findOrFail($this->laporanId);

            $service = app(DailyReportAggregationService::class);
            $results = $service->calculateForLaporan($laporan);

            $totalPenilaian = $results['total_penilaians'];
            $calculatedCount = $results['calculated'];
            $skippedCount = $results['skipped'];

            // Success notification
            Notification::make()
                ->title('✅ Perhitungan Berhasil Diselesaikan')
                ->body("Laporan **{$laporan->name}**\n\nBerhasil menghitung {$calculatedCount} dari {$totalPenilaian} penilaian.\n{$skippedCount} penilaian tidak memiliki data daily report.")
                ->success()
                ->duration(10000)
                ->send();

            Log::info("CalculateLaporanFromDailyReports [{$laporan->id}]: Completed successfully. Calculated: {$calculatedCount}, Skipped: {$skippedCount}");
        } catch (\Exception $e) {
            Log::error("CalculateLaporanFromDailyReports [{$this->laporanId}]: Error - " . $e->getMessage());

            // Error notification
            Notification::make()
                ->title('❌ Perhitungan Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
