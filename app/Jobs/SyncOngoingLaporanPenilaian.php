<?php

namespace App\Jobs;

use App\Services\Laporan\LaporanImutService;
use App\Models\LaporanImut;
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
        $laporan = $service->getLatestLaporan();
        
        // Hanya proses jika laporan bulan ini sedang aktif (PROCESS)
        if ($laporan && $laporan->status === LaporanImut::STATUS_PROCESS) {
            ProsesPenilaianImut::dispatch($laporan->id, true);
        }
    }
}
