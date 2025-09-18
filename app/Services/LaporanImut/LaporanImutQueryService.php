<?php

namespace App\Services\LaporanImut;

use App\Models\LaporanImut;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaporanImutQueryService
{
    public function __construct(
        private LaporanImutRepositoryInterface $laporanRepository
    ) {}

    /**
     * Get the latest active laporan
     */
    public function getLatestLaporan(): ?LaporanImut
    {
        try {
            $today = Carbon::today();

            // Try to get active laporan in current period
            $laporan = LaporanImut::select(['id', 'assessment_period_start', 'status'])
                ->where('status', LaporanImut::STATUS_PROCESS)
                ->whereDate('assessment_period_start', '<=', $today)
                ->whereDate('assessment_period_end', '>=', $today)
                ->orderByDesc('assessment_period_start')
                ->first();

            if ($laporan) {
                return $laporan;
            }

            // Fallback to latest completed laporan
            return LaporanImut::select(['id', 'assessment_period_start', 'status'])
                ->where('status', LaporanImut::STATUS_COMPLETE)
                ->latest('assessment_period_start')
                ->first();

        } catch (\Throwable $e) {
            Log::error('Failed to get latest laporan: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get recent laporan list with limit
     */
    public function getRecentLaporanList(int $limit): Collection
    {
        return LaporanImut::select(['id', 'name', 'assessment_period_start', 'status'])
            ->latest('assessment_period_start')
            ->limit($limit)
            ->get();
    }

    /**
     * Get laporan list with filters
     */
    public function getLaporanList(array $filters = [], ?int $limit = null): Collection
    {
        $query = LaporanImut::query();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('assessment_period_start', $filters['year']);
        }

        if (isset($filters['period_start'])) {
            $query->whereDate('assessment_period_start', '>=', $filters['period_start']);
        }

        if (isset($filters['period_end'])) {
            $query->whereDate('assessment_period_end', '<=', $filters['period_end']);
        }

        // Apply limit if specified
        if ($limit) {
            $query->limit($limit);
        }

        return $query->orderByDesc('assessment_period_start')->get();
    }

    /**
     * Get active indicators with profiles for given laporan IDs
     */
    public function getAktifIndikatorWithProfiles(Collection $laporanIds): Collection
    {
        return DB::table('imut_data')
            ->join('imut_profiles', 'imut_data.imut_profile_id', '=', 'imut_profiles.id')
            ->join('laporan_unit_kerjas', 'imut_data.id', '=', 'laporan_unit_kerjas.imut_data_id')
            ->whereIn('laporan_unit_kerjas.laporan_imut_id', $laporanIds)
            ->where('imut_profiles.is_active', true)
            ->select([
                'imut_profiles.*',
                'laporan_unit_kerjas.laporan_imut_id'
            ])
            ->get()
            ->groupBy('laporan_imut_id');
    }

    /**
     * Get grouped penilaian by specified criteria
     */
    public function getGroupedPenilaian(
        array $laporanIds,
        ?array $profileIds = null,
        string $groupBy = 'laporan_unit_kerjas.laporan_imut_id'
    ): Collection {
        $query = DB::table('imut_penilaians')
            ->join('laporan_unit_kerjas', 'imut_penilaians.laporan_unit_kerja_id', '=', 'laporan_unit_kerjas.id')
            ->whereIn('laporan_unit_kerjas.laporan_imut_id', $laporanIds)
            ->select([
                'imut_penilaians.*',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.unit_kerja_id'
            ]);

        if ($profileIds) {
            $query->whereIn('imut_penilaians.imut_profil_id', $profileIds);
        }

        return $query->get()->groupBy($groupBy);
    }

    /**
     * Get penilaian grouped by profile for specific laporan
     */
    public function getPenilaianGroupedByProfile(int $laporanId): Collection
    {
        return DB::table('imut_penilaians')
            ->join('laporan_unit_kerjas', 'imut_penilaians.laporan_unit_kerja_id', '=', 'laporan_unit_kerjas.id')
            ->join('imut_profiles', 'imut_penilaians.imut_profil_id', '=', 'imut_profiles.id')
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->where('imut_profiles.is_active', true)
            ->select([
                'imut_penilaians.*',
                'imut_profiles.name as profile_name',
                'imut_profiles.benchmarking_baseline',
                'laporan_unit_kerjas.unit_kerja_id'
            ])
            ->get()
            ->groupBy('imut_profil_id');
    }
}
