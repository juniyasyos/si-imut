<?php

namespace App\QueryBuilders;

use App\Models\LaporanUnitKerja;
use App\Services\ImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk detail laporan per IMUT Data
 */
class ImutDataDetailReportQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = LaporanUnitKerja::query();
    }

    /**
     * Build query untuk detail laporan berdasarkan IMUT data dengan validasi unit kerja
     *
     * @param int $laporanId
     * @param int $imutDataId
     * @param int|null $unitKerjaId - Tambahan parameter untuk cross-check unit kerja
     * @return Builder
     */
    public function build(int $laporanId, int $imutDataId, ?int $unitKerjaId = null): Builder
    {
        $query = $this->query
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->where('imut_profil.imut_data_id', $imutDataId);

        // VALIDASI KRITIS: Pastikan IMUT data dimiliki oleh unit kerja
        // Cross-check melalui relasi unit_kerja -> imutData
        $query->whereHas('unitKerja.imutData', function ($q) use ($imutDataId) {
            $q->where('imut_data.id', $imutDataId);
        });

        // Validasi tambahan jika unitKerjaId diberikan
        if ($unitKerjaId) {
            $query->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId);
        }

        return $query->select(
            'imut_penilaians.id as id',
            'laporan_unit_kerjas.laporan_imut_id',
            'laporan_unit_kerjas.id as laporan_unit_kerja_id',
            'laporan_unit_kerjas.unit_kerja_id',
            'unit_kerja.unit_name as unit_kerja',
            'imut_data.title as imut_data',
            'imut_kategori.short_name as imut_kategori',
            'imut_kategori.id as imut_kategori_id',
            'imut_profil.version as imut_profil',
            'imut_profil.target_value as imut_standard',
            'imut_profil.target_operator as imut_standard_type_operator',
            'imut_profil.valid_from',
            'imut_profil.valid_until',
            'imut_penilaians.numerator_value',
            'imut_penilaians.denominator_value',
            'imut_penilaians.recommendations',
            'imut_penilaians.analysis',
            DB::raw(ImutCalculationService::percentageExpression(
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value'
            ))
        );
    }
}
