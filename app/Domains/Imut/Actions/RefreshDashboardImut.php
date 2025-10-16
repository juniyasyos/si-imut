<?php

namespace App\Domains\Imut\Actions;

use App\Domains\Reporting\Services\LaporanImutService;
use App\Services\DashboardImutService;
use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;

class RefreshDashboardImut
{
    public function __construct(
        private readonly DashboardImutService $dashboardService,
        private readonly LaporanImutService $laporanService,
    ) {}

    /**
     * Flush dashboard caches and return the freshly computed payload.
     */
    public function execute(?int $laporanId = null): array
    {
        $targetId = $laporanId ?? $this->laporanService->getLatestLaporanId();

        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData());

        if ($targetId) {
            Cache::forget(CacheKey::dashboardSiimutAllData($targetId));
            Cache::forget(CacheKey::getPenilaianStats($targetId, false));
            Cache::forget(CacheKey::getPenilaianStats($targetId, true));
            Cache::forget(CacheKey::imutChartSeriesData($targetId));
        }

        return $this->dashboardService->getAllDashboardData();
    }
}
