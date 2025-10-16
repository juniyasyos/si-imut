<?php

namespace App\Domains\Imut\Queries;

use Illuminate\Support\Collection;

class ImutCapaianByUnitSpec
{
    /**
     * Aggregate numerator/denominator pairs per kategori untuk setiap laporan.
     *
     * @return array<int, array{name: string, points: array<int, array{numerator: float|int, denominator: float|int}>}>
     */
    public function build(Collection $laporans, Collection $categories): array
    {
        return $categories->map(function ($category) use ($laporans) {
            $points = $laporans->map(function ($laporan) use ($category) {
                $penilaians = $laporan->laporanUnitKerjas
                    ->flatMap(fn($laporanUnitKerja) => $laporanUnitKerja->imutPenilaians)
                    ->filter(fn($penilaian) => $penilaian->profile?->imutData?->imut_kategori_id === $category->id);

                if ($penilaians->isEmpty()) {
                    return ['numerator' => 0, 'denominator' => 0];
                }

                $numerator = $penilaians->sum('numerator_value');
                $denominator = $penilaians->sum('denominator_value');

                return [
                    'numerator' => $numerator,
                    'denominator' => $denominator,
                ];
            })->toArray();

            return [
                'name' => $category->short_name,
                'points' => $points,
            ];
        })->toArray();
    }
}
