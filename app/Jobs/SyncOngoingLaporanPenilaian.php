<?php

namespace App\Jobs;

use App\Models\LaporanImut;
use App\Services\Laporan\LaporanImutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncOngoingLaporanPenilaian implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(LaporanImutService $service): void
    {
        // Ambil semua laporan yang sedang dalam proses aktif
        $ongoingReports = LaporanImut::where('status', LaporanImut::STATUS_PROCESS)->get();

        if ($ongoingReports->isEmpty()) {
            $latest = $service->getLatestLaporan();
            if ($latest) {
                $ongoingReports = collect([$latest]);
            }
        }

        foreach ($ongoingReports as $laporan) {
            ProsesPenilaianImut::dispatch($laporan->id, true);
        }
    }
}
