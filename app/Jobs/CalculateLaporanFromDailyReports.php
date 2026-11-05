<?php

namespace App\Jobs;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Services\Reporting\DailyReportAggregationService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateLaporanFromDailyReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $laporanId) {}

    public function handle(): void
    {
        try {
            $laporan = LaporanImut::findOrFail($this->laporanId);

            // 1. CLEANUP PREVENTIF: Hapus orphaned penilaian yang relasi unitnya sudah dicabut
            $this->cleanupOrphanedPenilaian($laporan);

            // 2. Kalkulasi dari Daily Reports
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

    /**
     * Bersihkan penilaian yang relasi unit kerjanya sudah dicabut di master data
     */
    private function cleanupOrphanedPenilaian(LaporanImut $laporan): void
    {
        try {
            $orphanedIds = DB::table('laporan_unit_kerjas')
                ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
                ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
                ->where('laporan_unit_kerjas.laporan_imut_id', $laporan->id)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('imut_data_unit_kerja')
                        ->whereColumn('imut_data_unit_kerja.unit_kerja_id', 'laporan_unit_kerjas.unit_kerja_id')
                        ->whereColumn('imut_data_unit_kerja.imut_data_id', 'imut_profil.imut_data_id');
                })
                ->pluck('imut_penilaians.id')
                ->toArray();

            if (!empty($orphanedIds)) {
                // Hapus via Eloquent model agar trigger Observer pembersih cache
                ImutPenilaian::whereIn('id', $orphanedIds)->get()->each->delete();
                Log::info("CalculateLaporanFromDailyReports [{$laporan->id}]: Cleaned up " . count($orphanedIds) . " orphaned penilaians");
            }
        } catch (\Throwable $e) {
            Log::error("CalculateLaporanFromDailyReports [{$laporan->id}]: Failed cleaning up orphaned penilaians - " . $e->getMessage());
        }
    }
}
