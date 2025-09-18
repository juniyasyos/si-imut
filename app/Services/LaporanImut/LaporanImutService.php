<?php

namespace App\Services\LaporanImut;

use App\Models\LaporanImut;
use App\Services\LaporanImut\LaporanImutQueryService;
use App\Services\LaporanImut\LaporanImutCacheService;
use App\Services\LaporanImut\LaporanImutCalculationService;
use Illuminate\Support\Collection;

class LaporanImutService
{
    public function __construct(
        private LaporanImutQueryService $queryService,
        private LaporanImutCacheService $cacheService,
        private LaporanImutCalculationService $calculationService
    ) {}

    /**
     * Get latest laporan ID
     */
    public function getLatestLaporanId(): int
    {
        return $this->getLatestLaporan()?->id ?? 0;
    }

    /**
     * Get latest laporan with caching
     */
    public function getLatestLaporan(): ?LaporanImut
    {
        return $this->cacheService->getCachedLatestLaporan(
            fn() => $this->queryService->getLatestLaporan()
        );
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartDataForLastLaporan(int $limit = 6): array
    {
        return $this->cacheService->getCachedChartData(function () use ($limit) {
            // Get recent laporan list
            $laporanList = $this->cacheService->getCachedRecentLaporanList(
                fn() => $this->queryService->getRecentLaporanList($limit),
                $limit
            );

            // Get laporan IDs
            $laporanIds = $laporanList->pluck('id');

            // Get indicators and profiles
            $indikatorAktif = $this->queryService->getAktifIndikatorWithProfiles($laporanIds);
            $profileIds = $this->calculationService->getLatestProfileIds($indikatorAktif);

            // Get grouped assessments
            $penilaianByLaporan = $this->queryService->getGroupedPenilaian(
                laporanIds: $laporanIds->all(),
                groupBy: 'laporan_unit_kerjas.laporan_imut_id'
            );

            $penilaianByProfile = $this->queryService->getGroupedPenilaian(
                laporanIds: $laporanIds->all(),
                profileIds: $profileIds->all(),
                groupBy: 'imut_penilaians.imut_profil_id'
            );

            // Process chart data
            return $this->calculationService->processChartData(
                $laporanList,
                $indikatorAktif,
                $penilaianByLaporan,
                $penilaianByProfile
            );
        }, $limit);
    }

    /**
     * Get current laporan data with statistics
     */
    public function getCurrentLaporanData(LaporanImut $laporan): ?array
    {
        return $this->cacheService->getCachedLaporanData(
            $laporan->id,
            function () use ($laporan) {
                $laporanId = $laporan->id;

                // Get indicators and profiles
                $indikatorAktif = $this->queryService->getAktifIndikatorWithProfiles(collect([$laporanId]));
                $profileIds = $this->calculationService->getLatestProfileIds($indikatorAktif)->all();

                // Get grouped assessments
                $penilaianByProfile = $this->queryService->getGroupedPenilaian(
                    laporanIds: [$laporanId],
                    profileIds: $profileIds,
                    groupBy: 'imut_penilaians.imut_profil_id'
                );

                $allPenilaian = $penilaianByProfile->flatten();

                // Calculate statistics
                $stats = $this->calculationService->calculateDashboardStats(
                    $indikatorAktif,
                    $penilaianByProfile,
                    $allPenilaian,
                    $laporanId
                );

                // Get total unit count
                $laporan->loadCount('unitKerjas');
                $stats['totalUnit'] = $laporan->unit_kerjas_count;

                return $stats;
            }
        );
    }

    /**
     * Get penilaian grouped by profile
     */
    public function getPenilaianGroupedByProfile(int $laporanId): Collection
    {
        return $this->queryService->getPenilaianGroupedByProfile($laporanId);
    }

    /**
     * Get laporan list with filters
     */
    public function getLaporanList(array $filters = [], ?int $limit = null): Collection
    {
        return $this->queryService->getLaporanList($filters, $limit);
    }

    /**
     * Clear cache when laporan is updated
     */
    public function clearCache(?int $laporanId = null): void
    {
        $this->cacheService->clearLaporanCaches($laporanId);
    }

    /**
     * Clear cache on penilaian update
     */
    public function clearCacheOnPenilaianUpdate(): void
    {
        $this->cacheService->clearCacheOnPenilaianUpdate();
    }
}
