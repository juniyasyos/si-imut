<?php

namespace App\Repositories\Interfaces;

use App\Models\ImutPenilaian;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface ImutPenilaianRepositoryInterface
{
    public function getByLaporanId(int $laporanId): Collection;

    public function getFilledByLaporan(int $laporanId): Collection;

    public function getByProfileId(int $profileId): Collection;

    public function groupByProfileId(int $laporanId): Collection;

    public function groupByUnitKerja(int $laporanId): Collection;

    public function countBelumDinilai(int $laporanId): int;

    public function summaryByLaporan(int $laporanId): array;

    public function summaryByUnitKerja(int $laporanId): Collection;

    public function summaryByProfile(int $laporanId): Collection;

    public function getByCategoryPeriod(array $categoryIds, array $monthStrings, Carbon $startDate, Carbon $endDate): Collection;

    public function getByUnitKerjaIds(array $unitKerjaIds): Collection;

    public function findLatestAnalysisForProfile(
        int $profileId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $unitKerjaId = null
    ): ?ImutPenilaian;

    public function updateCalculation(ImutPenilaian $penilaian, array $result): bool;
}