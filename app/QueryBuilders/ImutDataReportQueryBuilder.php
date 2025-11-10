<?php

namespace App\QueryBuilders;

use App\Models\LaporanUnitKerja;
use App\Services\ImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk laporan per IMUT Data
 */
class ImutDataReportQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = LaporanUnitKerja::query();
    }

    /**
     * Build query untuk laporan berdasarkan IMUT data
     *
     * @param int $laporanId
     * @return Builder
     */
    public function build(int $laporanId): Builder
    {
        return $this->query
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->leftJoin('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->leftJoin('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->leftJoin('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->select(
                'imut_data.id as id',
                'imut_data.title as imut_data_title',
                'laporan_unit_kerjas.laporan_imut_id',
                'imut_kategori.short_name as imut_kategori',
                'imut_kategori.id as imut_kategori_id',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                DB::raw(ImutCalculationService::sumExpression('imut_penilaians.numerator_value', 'total_numerator')),
                DB::raw(ImutCalculationService::sumExpression('imut_penilaians.denominator_value', 'total_denominator')),
                DB::raw(ImutCalculationService::percentageExpression(
                    'SUM(imut_penilaians.numerator_value)',
                    'NULLIF(SUM(imut_penilaians.denominator_value), 0)'
                )),
                DB::raw(ImutCalculationService::filledCountExpression('imut_penilaians.numerator_value', 'imut_penilaians.denominator_value')),
                DB::raw(ImutCalculationService::completionPercentageExpression(
                    "SUM(CASE WHEN imut_penilaians.numerator_value IS NOT NULL AND imut_penilaians.denominator_value IS NOT NULL AND imut_penilaians.denominator_value != 0 THEN 1 ELSE 0 END)",
                    'COUNT(imut_penilaians.id)',
                    'percentage_units'
                )),
                DB::raw('COUNT(imut_penilaians.id) as total_count'),
            )
            ->groupBy(
                'imut_data.id',
                'imut_data.title',
                'laporan_unit_kerjas.laporan_imut_id',
                'imut_kategori.short_name',
                'imut_kategori.id',
                'imut_profil.target_value',
                'imut_profil.target_operator'
            )
            ->orderBy('imut_data.title');
    }
}
