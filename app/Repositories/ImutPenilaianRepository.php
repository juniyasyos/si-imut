<?php

namespace App\Repositories;

use App\Models\ImutPenilaian;
use App\Repositories\Interfaces\ImutPenilaianRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImutPenilaianRepository implements ImutPenilaianRepositoryInterface
{
    public function getByLaporanId(int $laporanId): Collection
    {
        return ImutPenilaian::with(['profile', 'laporanUnitKerja'])
            ->whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
            ->get();
    }

    public function getFilledByLaporan(int $laporanId): Collection
    {
        return ImutPenilaian::with(['profile', 'laporanUnitKerja'])
            ->whereNotNull('numerator_value')
            ->whereNotNull('denominator_value')
            ->whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
            ->get();
    }

    public function getByProfileId(int $profileId): Collection
    {
        return ImutPenilaian::with(['laporanUnitKerja', 'profile'])
            ->where('imut_profil_id', $profileId)
            ->get();
    }

    public function groupByProfileId(int $laporanId): Collection
    {
        return $this->getFilledByLaporan($laporanId)
            ->groupBy('imut_profil_id');
    }

    public function groupByUnitKerja(int $laporanId): Collection
    {
        return $this->getFilledByLaporan($laporanId)
            ->groupBy('laporan_unit_kerja_id');
    }

    public function countBelumDinilai(int $laporanId): int
    {
        return ImutPenilaian::whereHas('laporanUnitKerja', fn ($q) => $q->where('laporan_imut_id', $laporanId))
            ->where(function ($q) {
                $q->whereNull('numerator_value')->orWhereNull('denominator_value');
            })
            ->count();
    }

    public function summaryByLaporan(int $laporanId): array
    {
        $data = $this->getFilledByLaporan($laporanId);

        $numerator = $data->sum('numerator_value');
        $denominator = $data->sum('denominator_value');
        $percentage = $denominator > 0 ? ceil(($numerator / $denominator) * 100 * 100) / 100 : null;

        return compact('numerator', 'denominator', 'percentage');
    }

    public function summaryByUnitKerja(int $laporanId): Collection
    {
        return $this->groupByUnitKerja($laporanId)->map(function ($items, $unitKerjaId) {
            $numerator = $items->sum('numerator_value');
            $denominator = $items->sum('denominator_value');
            $percentage = $denominator > 0 ? ceil(($numerator / $denominator) * 100 * 100) / 100 : null;

            return compact('unitKerjaId', 'numerator', 'denominator', 'percentage');
        })->values();
    }

    public function summaryByProfile(int $laporanId): Collection
    {
        return $this->groupByProfileId($laporanId)->map(function ($items, $profileId) {
            $numerator = $items->sum('numerator_value');
            $denominator = $items->sum('denominator_value');
            $percentage = $denominator > 0 ? ceil(($numerator / $denominator) * 100 * 100) / 100 : null;

            return compact('profileId', 'numerator', 'denominator', 'percentage');
        })->values();
    }

    public function getByCategoryPeriod(array $categoryIds, array $monthStrings, Carbon $startDate, Carbon $endDate): Collection
    {
        $query = ImutPenilaian::with(['profile.imutData', 'laporanUnitKerja.laporanImut'])
            ->whereHas('profile.imutData', fn ($q) => $q->where('status', true));

        if (count($categoryIds) > 0) {
            $query->whereHas('profile.imutData.categories', fn ($q) => $q->whereIn('id', $categoryIds));
        }

        if (count($monthStrings) > 0) {
            $query->whereHas('laporanUnitKerja.laporanImut', function ($q) use ($monthStrings, $startDate, $endDate) {
                $q->whereIn(DB::raw("CONCAT(report_year,'-',LPAD(report_month,2,'0'))"), $monthStrings)
                    ->orWhereBetween('assessment_period_start', [$startDate, $endDate])
                    ->orWhereBetween('assessment_period_end', [$startDate, $endDate])
                    ->orWhere(function ($q3) use ($startDate, $endDate) {
                        $q3->where('assessment_period_start', '<=', $startDate)
                            ->where('assessment_period_end', '>=', $endDate);
                    });
            });
        }

        return $query->get();
    }

    public function getByUnitKerjaIds(array $unitKerjaIds): Collection
    {
        return ImutPenilaian::with('profile')
            ->whereHas('laporanUnitKerja', fn ($q) => $q->whereIn('unit_kerja_id', $unitKerjaIds))
            ->get();
    }

    public function findLatestAnalysisForProfile(
        int $profileId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $unitKerjaId = null
    ): ?ImutPenilaian {
        $query = ImutPenilaian::query()
            ->with(['laporanUnitKerja'])
            ->where('imut_profil_id', $profileId)
            ->whereHas('laporanUnitKerja.laporanImut', function ($q) use ($startDate, $endDate) {
                $q->where(function ($nested) use ($startDate, $endDate) {
                    $nested->whereBetween('assessment_period_start', [$startDate, $endDate])
                        ->orWhereBetween('assessment_period_end', [$startDate, $endDate]);
                });
            });

        if ($unitKerjaId) {
            $query->whereHas('laporanUnitKerja', fn ($q) => $q->where('unit_kerja_id', $unitKerjaId));
        }

        return $query->latest('id')->first();
    }

    public function updateCalculation(ImutPenilaian $penilaian, array $result): bool
    {
        return $penilaian->update([
            'numerator_value' => $result['numerator'],
            'denominator_value' => $result['denominator'],
            'is_auto_calculated' => true,
            'calculation_metadata' => $result['calculation_metadata'],
        ]);
    }
}