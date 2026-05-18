<?php

namespace App\QueryBuilders;

use App\Models\ImutPenilaian;
use App\Services\Core\ImutSqlExpressionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk detail laporan per Unit Kerja
 */
class UnitKerjaDetailReportQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = ImutPenilaian::query();
    }

    /**
     * Build query untuk detail laporan berdasarkan unit kerja
     *
     * @param int $laporanId
     * @param int $unitKerjaId
     * @return Builder
     */
    public function build(int $laporanId, int $unitKerjaId): Builder
    {
        return $this->query
            ->select([
                'imut_penilaians.*',
                'laporan_unit_kerjas.unit_kerja_id',
                'laporan_unit_kerjas.laporan_imut_id',
                'unit_kerja.unit_name',
                'imut_data.title as imut_data',
                'imut_data.is_monthly',
                'imut_kategori.short_name as imut_kategori',
                'imut_kategori.id as imut_kategori_id',
                'imut_profil.version as imut_profil',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                'imut_profil.valid_from',
                'imut_profil.valid_until',
                DB::raw(ImutCalculationService::percentageExpression(
                    'imut_penilaians.numerator_value',
                    'imut_penilaians.denominator_value'
                )),
            ])
            ->join('laporan_unit_kerjas', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId);
    }
}
