<?php

namespace App\QueryBuilders;

use App\Models\LaporanUnitKerja;
use App\Models\RegionType;
use App\Services\ImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk summary IMUT data dengan benchmarking (grouped by laporan)
 */
class ImutDataGroupedSummaryQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = LaporanUnitKerja::query();
    }

    /**
     * Build query untuk summary IMUT data dengan dynamic benchmarking columns
     *
     * @param int $imutDataId
     * @return Builder
     */
    public function build(int $imutDataId): Builder
    {
        $regionTypes = RegionType::all();

        $selectFields = [
            'laporan_imuts.id as laporan_imut_id',
            'laporan_imuts.name as laporan_name',
            'laporan_imuts.status as laporan_status',
            'laporan_imuts.assessment_period_start',
            'laporan_imuts.assessment_period_end',
            'imut_kategori.id as imut_kategori_id',
            DB::raw('MAX(imut_profil.target_value) as imut_standard'),
            DB::raw('MAX(imut_profil.target_operator) as imut_standard_type_operator'),
            DB::raw('CONCAT(
                DATE_FORMAT(laporan_imuts.assessment_period_start, "%d %M %Y"),
                " - ",
                DATE_FORMAT(laporan_imuts.assessment_period_end, "%d %M %Y")
            ) as periode_pengisian'),
            DB::raw(ImutCalculationService::sumExpression('imut_penilaians.numerator_value', 'total_numerator')),
            DB::raw(ImutCalculationService::sumExpression('imut_penilaians.denominator_value', 'total_denominator')),
            DB::raw('COUNT(DISTINCT laporan_unit_kerjas.unit_kerja_id) as unit_count'),
            DB::raw(ImutCalculationService::percentageExpression(
                'SUM(imut_penilaians.numerator_value)',
                'NULLIF(SUM(imut_penilaians.denominator_value), 0)'
            )),
        ];

        // Add dynamic benchmarking columns for each region type
        foreach ($regionTypes as $regionType) {
            $selectFields[] = DB::raw("
                MAX(CASE
                    WHEN region_types.id = {$regionType->id}
                    THEN imut_benchmarkings.benchmark_value
                    ELSE NULL
                END) as benchmark_{$regionType->id}
            ");
        }

        return $this->query
            ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->leftJoin('imut_benchmarkings', function ($join) {
                $join->on('imut_data.id', '=', 'imut_benchmarkings.imut_data_id')
                    ->where('imut_benchmarkings.is_active', '=', 1)
                    ->whereRaw('laporan_imuts.assessment_period_start >= imut_benchmarkings.period_start')
                    ->where(function ($query) {
                        $query->whereNull('imut_benchmarkings.period_end')
                            ->orWhereRaw('laporan_imuts.assessment_period_end <= imut_benchmarkings.period_end');
                    });
            })
            ->leftJoin('region_types', 'imut_benchmarkings.region_type_id', '=', 'region_types.id')
            ->where('imut_data.id', $imutDataId)
            ->groupBy([
                'laporan_imuts.id',
                'laporan_imuts.name',
                'laporan_imuts.status',
                'laporan_imuts.assessment_period_start',
                'laporan_imuts.assessment_period_end',
                'imut_kategori.id'
            ])
            ->select($selectFields)
            ->orderBy('laporan_imuts.assessment_period_start', 'desc');
    }
}
