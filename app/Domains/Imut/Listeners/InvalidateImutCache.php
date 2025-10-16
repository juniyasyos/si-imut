<?php

namespace App\Domains\Imut\Listeners;

use App\Domains\Imut\Actions\RefreshDashboardImut;
use App\Domains\Imut\Events\ImutPenilaianSubmitted;

class InvalidateImutCache
{
    public function __construct(private readonly RefreshDashboardImut $refreshDashboard)
    {
    }

    public function handle(ImutPenilaianSubmitted $event): void
    {
        $penilaian = $event->penilaian;

        $penilaian->clearCache();

        $penilaian->loadMissing('laporanUnitKerja');
        $laporanId = $penilaian->laporanUnitKerja?->laporan_imut_id;

        $this->refreshDashboard->execute($laporanId);
    }
}
