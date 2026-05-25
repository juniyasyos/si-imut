<?php

namespace App\Services\Reporting;

use App\Repositories\Interfaces\ImutPenilaianRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UnitKerjaStatService
{
    public function __construct(
        protected ImutPenilaianRepositoryInterface $penilaianRepository
    ) {}

    public function getStats(): array
    {
        $user = Auth::user();
        $unitKerjaIds = $user->unitKerjas->pluck('id');

        $penilaians = $this->penilaianRepository->getByUnitKerjaIds($unitKerjaIds->all());

        $totalIndikator = $penilaians->pluck('imut_profil_id')->unique()->count();

        $averagePercentage = $penilaians->avg(function ($penilaian) {
            return $penilaian->denominator_value > 0
                ? ceil(($penilaian->numerator_value / $penilaian->denominator_value) * 100 * 100) / 100
                : 0;
        });

        $jumlahMemenuhiTarget = $penilaians->filter(function ($penilaian) {
            $profil = $penilaian->profile;
            if (! $profil || $penilaian->denominator_value == 0) {
                return false;
            }

            $value = ceil(($penilaian->numerator_value / $penilaian->denominator_value) * 100 * 100) / 100;

            return match ($profil->target_operator) {
                '>=' => $value >= $profil->target_value,
                '<=' => $value <= $profil->target_value,
                '=' => round($value, 2) === round($profil->target_value, 2),
                default => false,
            };
        })->count();

        return [
            'totalIndikator' => $totalIndikator,
            'averagePercentage' => $averagePercentage,
            'jumlahMemenuhiTarget' => $jumlahMemenuhiTarget,
        ];
    }
}
