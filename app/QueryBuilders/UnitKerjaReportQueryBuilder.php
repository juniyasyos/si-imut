<?php

namespace App\QueryBuilders;

use App\Models\LaporanUnitKerja;
use App\Services\Core\ImutCalculationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Query builder untuk laporan per Unit Kerja (mendukung 1 laporan maupun multi-laporan per periode).
 */
class UnitKerjaReportQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = LaporanUnitKerja::query();
    }

    /**
     * Build query untuk laporan berdasarkan unit kerja.
     *
     * @param int|int[] $laporanIds
     * @return Builder
     */
    public function build(int|array $laporanIds): Builder
    {
        $ids = is_array($laporanIds)
            ? array_values(array_filter(array_map('intval', $laporanIds)))
            : [$laporanIds];

        $isMultiPeriod = is_array($laporanIds);

        ['filled' => $filledCountExpr, 'meets' => $meetsStandardExpr, 'below' => $belowStandardExpr, 'empty' => $emptyCountExpr] = self::getStandardExpressions();

        if (empty($ids)) {
            $this->query->whereRaw('1 = 0');
        } elseif ($isMultiPeriod) {
            $this->query->whereIn('laporan_unit_kerjas.laporan_imut_id', $ids);
        } else {
            $this->query->where('laporan_unit_kerjas.laporan_imut_id', $ids[0]);
        }

        $this->query
            ->leftJoin('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->leftJoin('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->leftJoin('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->select([
                $isMultiPeriod ? DB::raw('MAX(laporan_unit_kerjas.id) as id') : 'laporan_unit_kerjas.id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                $isMultiPeriod ? DB::raw('MAX(laporan_unit_kerjas.laporan_imut_id) as laporan_imut_id') : 'laporan_unit_kerjas.laporan_imut_id',
                DB::raw('MAX(imut_profil.target_value) as imut_standard'),
                DB::raw('MAX(imut_profil.target_operator) as imut_standard_type_operator'),
                DB::raw("{$filledCountExpr} as filled_count"),
                DB::raw('COUNT(imut_penilaians.id) as total_count'),
                DB::raw("ROUND(
                    CASE WHEN COUNT(imut_penilaians.id) > 0
                    THEN {$filledCountExpr} * 100.0 / COUNT(imut_penilaians.id)
                    ELSE 0 END, 2
                ) as percentage"),
                // Indikator yang memenuhi standar
                DB::raw("{$meetsStandardExpr} as meets_standard_count"),
                // Hitung jumlah IMUT yang terisi tapi tidak memenuhi standar
                DB::raw("{$belowStandardExpr} as below_standard_count"),
                // Hitung jumlah IMUT yang kosong / belum terisi
                DB::raw("{$emptyCountExpr} as empty_count"),
                // Persentase capaian: indikator standar / jumlah indikator * 100%
                DB::raw("ROUND(
                    CASE WHEN COUNT(imut_penilaians.id) > 0
                    THEN ({$meetsStandardExpr}) * 100.0 / COUNT(imut_penilaians.id)
                    ELSE 0 END, 2
                ) as standard_percentage"),
            ]);

        if ($isMultiPeriod) {
            return $this->query->groupBy(
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name'
            );
        }

        return $this->query->groupBy(
            'laporan_unit_kerjas.id',
            'laporan_unit_kerjas.unit_kerja_id',
            'unit_kerja.unit_name',
            'laporan_unit_kerjas.laporan_imut_id'
        );
    }

    /**
     * Build query rincian capaian per unit kerja dan per bulan (untuk export Excel periode).
     *
     * @param  int|int[]  $laporanIds
     * @param  int[]  $categoryIds
     * @param  int[]  $unitKerjaIds
     */
    public static function buildMonthlyBreakdown(
        int|array $laporanIds,
        array $categoryIds = [],
        array $unitKerjaIds = []
    ): Builder {
        $ids = is_array($laporanIds)
            ? array_values(array_filter(array_map('intval', $laporanIds)))
            : [(int) $laporanIds];

        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));
        $unitKerjaIds = array_values(array_filter(array_map('intval', $unitKerjaIds)));

        ['meets' => $meetsStandardExpr, 'below' => $belowStandardExpr, 'empty' => $emptyCountExpr] = self::getStandardExpressions();
        $monthExpr = 'COALESCE(MONTH(laporan_imuts.assessment_period_start), laporan_imuts.report_month)';

        $query = LaporanUnitKerja::query()
            ->join('laporan_imuts', 'laporan_unit_kerjas.laporan_imut_id', '=', 'laporan_imuts.id')
            ->leftJoin('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->leftJoin('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->leftJoin('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id');

        if (empty($ids)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('laporan_unit_kerjas.laporan_imut_id', $ids);
        }

        if (! empty($categoryIds)) {
            $query->leftJoin('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
                ->whereIn('imut_data.imut_kategori_id', $categoryIds);
        }

        if (! empty($unitKerjaIds)) {
            $query->whereIn('laporan_unit_kerjas.unit_kerja_id', $unitKerjaIds);
        }

        return $query
            ->select([
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                DB::raw("{$monthExpr} as report_month"),
                DB::raw('COUNT(imut_penilaians.id) as total_count'),
                DB::raw("{$meetsStandardExpr} as meets_standard_count"),
                DB::raw("{$belowStandardExpr} as below_standard_count"),
                DB::raw("{$emptyCountExpr} as empty_count"),
                DB::raw("ROUND(
                    CASE WHEN COUNT(imut_penilaians.id) > 0
                    THEN ({$meetsStandardExpr}) * 100.0 / COUNT(imut_penilaians.id)
                    ELSE 0 END, 2
                ) as standard_percentage"),
            ])
            ->groupBy(
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                DB::raw($monthExpr)
            )
            ->orderBy('unit_kerja.unit_name')
            ->orderByRaw($monthExpr);
    }

    /**
     * Ekspresi SQL standar untuk indikator terisi, memenuhi standar, tidak sesuai standar, dan kosong.
     *
     * @return array{filled: string, meetsCondition: string, meets: string, below: string, empty: string}
     */
    protected static function getStandardExpressions(): array
    {
        $filledCountExpr = self::getFilledCountExpression();

        $meetsStandardCondition = ImutCalculationService::meetsStandardExpression(
            ImutCalculationService::percentageExpression('imut_penilaians.numerator_value', 'imut_penilaians.denominator_value'),
            'imut_profil.target_value',
            'imut_profil.target_operator'
        );

        $meetsStandardExpr = "SUM(CASE
            WHEN imut_penilaians.numerator_value IS NOT NULL
                AND imut_penilaians.denominator_value IS NOT NULL
                AND imut_penilaians.denominator_value != 0
                AND {$meetsStandardCondition}
            THEN 1 ELSE 0 END)";

        $belowStandardExpr = "SUM(CASE
            WHEN imut_penilaians.numerator_value IS NOT NULL
                AND imut_penilaians.denominator_value IS NOT NULL
                AND imut_penilaians.denominator_value != 0
                AND NOT {$meetsStandardCondition}
            THEN 1 ELSE 0 END)";

        $emptyCountExpr = "SUM(CASE
            WHEN imut_penilaians.numerator_value IS NULL
                OR imut_penilaians.denominator_value IS NULL
                OR imut_penilaians.denominator_value = 0
            THEN 1 ELSE 0 END)";

        return [
            'filled' => $filledCountExpr,
            'meetsCondition' => $meetsStandardCondition,
            'meets' => $meetsStandardExpr,
            'below' => $belowStandardExpr,
            'empty' => $emptyCountExpr,
        ];
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
