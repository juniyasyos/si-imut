<?php

namespace App\QueryBuilders;

use App\Models\LaporanUnitKerja;
use App\Services\ImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk laporan per Unit Kerja
 */
class UnitKerjaReportQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = LaporanUnitKerja::query();
    }

    /**
     * Build query untuk laporan berdasarkan unit kerja
     *
     * @param int $laporanId
     * @return Builder
     */
    public function build(int $laporanId): Builder
    {
        // Expression untuk menghitung filled_count - akan digunakan di SELECT dan HAVING
        $filledCountExpr = "SUM(CASE
            WHEN imut_penilaians.numerator_value IS NOT NULL
            AND imut_penilaians.denominator_value IS NOT NULL
            AND imut_penilaians.denominator_value != 0
            THEN 1 ELSE 0 END)";

        return $this->query
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->leftJoin('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->leftJoin('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->leftJoin('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->select([
                'laporan_unit_kerjas.id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'laporan_unit_kerjas.laporan_imut_id',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                DB::raw("{$filledCountExpr} as filled_count"),
                DB::raw('COUNT(imut_penilaians.id) as total_count'),
                DB::raw("ROUND(
                    CASE WHEN COUNT(imut_penilaians.id) > 0
                    THEN {$filledCountExpr} * 100.0 / COUNT(imut_penilaians.id)
                    ELSE 0 END, 2
                ) as percentage"),
                // Hitung jumlah IMUT yang terisi tapi tidak memenuhi standar
                DB::raw("SUM(CASE
                    WHEN imut_penilaians.numerator_value IS NOT NULL
                        AND imut_penilaians.denominator_value IS NOT NULL
                        AND imut_penilaians.denominator_value != 0
                        AND NOT " . ImutCalculationService::meetsStandardExpression(
                            ImutCalculationService::percentageExpression('imut_penilaians.numerator_value', 'imut_penilaians.denominator_value'),
                            'imut_profil.target_value',
                            'imut_profil.target_operator'
                        ) . "
                    THEN 1 ELSE 0 END) as below_standard_count"),
            ])
            ->groupBy(
                'laporan_unit_kerjas.id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'laporan_unit_kerjas.laporan_imut_id'
            );
    }

    /**
     * Get filled count expression untuk digunakan di HAVING clause
     *
     * @return string
     */
    public static function getFilledCountExpression(): string
    {
        return "SUM(CASE
            WHEN imut_penilaians.numerator_value IS NOT NULL
            AND imut_penilaians.denominator_value IS NOT NULL
            AND imut_penilaians.denominator_value != 0
            THEN 1 ELSE 0 END)";
    }
}
