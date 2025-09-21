<?php

namespace App\Services\LaporanImut;

use App\Models\LaporanImut;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LaporanImutService
{
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
        return Cache::remember('latest_laporan', 300, function () {
            return LaporanImut::latest('created_at')->first();
        });
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartDataForLastLaporan(int $limit = 6): array
    {
        return Cache::remember("chart_data_last_{$limit}", 600, function () use ($limit) {
            $laporanList = LaporanImut::latest('created_at')
                ->limit($limit)
                ->get();

            if ($laporanList->isEmpty()) {
                return [];
            }

            $chartData = [];

            foreach ($laporanList as $laporan) {
                $penilaianCount = ImutPenilaian::whereHas('laporanUnitKerja', function ($query) use ($laporan) {
                    $query->where('laporan_imut_id', $laporan->id);
                })->count();

                $chartData[] = [
                    'laporan_id' => $laporan->id,
                    'periode' => $laporan->periode_bulan . '/' . $laporan->periode_tahun,
                    'total_penilaian' => $penilaianCount,
                    'created_at' => $laporan->created_at,
                ];
            }

            return $chartData;
        });
    }

    /**
     * Get current laporan data with statistics
     */
    public function getCurrentLaporanData(LaporanImut $laporan): ?array
    {
        return Cache::remember("laporan_data_{$laporan->id}", 300, function () use ($laporan) {
            $laporan->loadCount('unitKerjas');

            $totalPenilaian = ImutPenilaian::whereHas('laporanUnitKerja', function ($query) use ($laporan) {
                $query->where('laporan_imut_id', $laporan->id);
            })->count();

            $indikatorAktif = ImutData::where('is_active', true)->count();

            return [
                'laporan' => $laporan,
                'totalUnit' => $laporan->unit_kerjas_count,
                'totalPenilaian' => $totalPenilaian,
                'indikatorAktif' => $indikatorAktif,
                'periode' => $laporan->periode_bulan . '/' . $laporan->periode_tahun,
            ];
        });
    }

    /**
     * Get penilaian grouped by profile
     */
    public function getPenilaianGroupedByProfile(int $laporanId): Collection
    {
        return ImutPenilaian::whereHas('laporanUnitKerja', function ($query) use ($laporanId) {
            $query->where('laporan_imut_id', $laporanId);
        })
        ->with(['imutProfile', 'laporanUnitKerja'])
        ->get()
        ->groupBy('imut_profil_id');
    }

    /**
     * Get laporan list with filters
     */
    public function getLaporanList(array $filters = [], ?int $limit = null): Collection
    {
        $query = LaporanImut::query();

        if (isset($filters['tahun'])) {
            $query->where('periode_tahun', $filters['tahun']);
        }

        if (isset($filters['bulan'])) {
            $query->where('periode_bulan', $filters['bulan']);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->latest('created_at')->get();
    }

    /**
     * Clear cache when laporan is updated
     */
    public function clearCache(?int $laporanId = null): void
    {
        Cache::forget('latest_laporan');
        Cache::forget('chart_data_last_6');

        if ($laporanId) {
            Cache::forget("laporan_data_{$laporanId}");
        }
    }

    /**
     * Clear cache on penilaian update
     */
    public function clearCacheOnPenilaianUpdate(): void
    {
        Cache::flush(); // Simple approach for comprehensive cache clear
    }
}
