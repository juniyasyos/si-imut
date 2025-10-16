<?php

namespace App\Domains\Reporting\Listeners;

use App\Domains\Imut\Actions\RefreshDashboardImut;
use App\Domains\Reporting\Events\LaporanGenerated;

class InvalidateLaporanCache
{
    public function __construct(private readonly RefreshDashboardImut $refreshDashboard)
    {
    }

    public function handle(LaporanGenerated $event): void
    {
        $laporan = $event->laporan;

        $laporan->clearCache();

        $this->refreshDashboard->execute($laporan->id);
    }
}
