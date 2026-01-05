<?php

namespace App\QueryBuilders;

use App\Models\LaporanUnitKerja;
use App\Services\ImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk laporan berdasarkan Unit Kerja (multi-period view)
 */
class LaporanByUnitQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = LaporanUnitKerja::query();
    }

    /**
     * Build query untuk laporan berdasarkan IMUT data dan unit kerja
     *
     * @param int $imutDataId
     * @param int $unitKerjaId
     * @return Builder
     */
    public function build(int $imutDataId, int $unitKerjaId): Builder
    {
        return $this->query
            ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('imut_data.id', $imutDataId)
            ->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId)
            ->select(
                'imut_penilaians.id as id',
                'laporan_unit_kerjas.id as laporan_unit_kerja_id',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.unit_kerja_id',
                'laporan_imuts.name as laporan_name',
                'laporan_imuts.status as laporan_status',
                'unit_kerja.unit_name',
                'imut_data.title as imut_data',
                'imut_kategori.short_name as imut_kategori',
                'imut_profil.version as imut_profil',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                'imut_profil.valid_from',
                'imut_profil.valid_until',
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'imut_penilaians.recommendations',
                'imut_penilaians.analysis',
                'imut_kategori.id as imut_kategori_id',
                DB::raw(ImutCalculationService::percentageExpression(
                    'imut_penilaians.numerator_value',
                    'imut_penilaians.denominator_value'
                ))
            );
    }
}
