<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class CacheKey
{
    public static function laporanImutDetail(int $laporanId, int $imutDataId): string
    {
        return "laporan:imut:detail:{$laporanId}:imut_data:{$imutDataId}";
    }

    public static function laporanUnitDetail(int $laporanId, int $unitKerjaId): string
    {
        return "laporan:imut:detail:{$laporanId}:unit:{$unitKerjaId}";
    }

    public static function imutLaporans(): string
    {
        return 'imut:laporans';
    }

    public static function dashboardSiimutAllData(int $laporanId): string
    {
        return "dashboard:siimut:all_data:{$laporanId}";
    }

    public static function latestLaporan(): string
    {
        return 'laporan:latest';
    }

    public static function dashboardSiimutChartData(): string
    {
        return 'dashboard:siimut:chart_data_dashboard';
    }

    public static function imutPenilaian(int $imutDataId, int|string $year, ?int $startMonth = null, ?int $endMonth = null): string
    {
        // Support both single year (int) and date range (string)
        $yearPart = is_string($year) ? $year : (string) $year;
        $startPart = $startMonth ? ":{$startMonth}" : '';
        $endPart = $endMonth ? ":{$endMonth}" : '';
        return "imut:penilaian:{$imutDataId}:{$yearPart}{$startPart}{$endPart}";
    }

    /**
     * Cache key untuk benchmarking IMUT.
     *
     * @param int               $year
     * @param array<int>|int|null $regionTypeId  null = semua region
     * @param array<int>|int|null $imutDataId    null = semua indikator
     * @param int|null          $endMonth        null = semua bulan
     */
    public static function imutBenchmarking(
        int $year,
        array|int|null $regionTypeId = null,
        array|int|null $imutDataId = null,
        ?int $endMonth = null
    ): string {
        $regionPart = is_array($regionTypeId)
            ? implode(',', $regionTypeId)
            : ($regionTypeId ?? 'all');

        $imutPart = is_array($imutDataId)
            ? implode(',', $imutDataId)
            : ($imutDataId ?? 'all');

        $monthPart = $endMonth ?? 'all';

        return "imut:benchmarking:{$year}:month:{$monthPart}:region:{$regionPart}:imut:{$imutPart}";
    }

    /**
     * Invalidate semua kombinasi cache benchmarking untuk indikator & tahun tertentu.
     * Menyasar kombinasi month [1..12, all] dan region [spesifik/null].
     */
    public static function invalidateBenchmarkingCache(
        int $imutDataId,
        int $year,
        ?int $regionTypeId = null
    ): void {
        // Kumpulan bulan: 1..12 + null (artinya 'all')
        $months = array_merge(range(1, 12), [null]);

        // Kumpulan region: pakai yang spesifik (jika ada) dan null (semua)
        $regions = array_unique([$regionTypeId, null], SORT_REGULAR);

        foreach ($months as $month) {
            foreach ($regions as $region) {
                Cache::forget(
                    static::imutBenchmarking($year, $region, $imutDataId, is_int($month) ? $month : null)
                );
            }
        }
    }

    /**
     * NOTE: dipertahankan sesuai format lama (underscore) agar tidak mematahkan cache yang ada.
     */
    public static function imutPenilaianImutDataUnitKerja(
        int $imutDataId,
        int $year,
        ?int $unitKerjaId = null,
        int $endMonth = 12
    ): string {
        return 'imut_penilaian_' . $imutDataId . '_' . $year
            . ($unitKerjaId ? '_uk_' . $unitKerjaId : '')
            . '_end_month_' . $endMonth;
    }

    public static function recentLaporanList(int $limit = 6): string
    {
        return "laporan.recent_list.limit_{$limit}";
    }

    public static function penilaianGroupedByProfile(int $laporanId): string
    {
        return 'penilaian_grouped_profile_' . $laporanId;
    }

    public static function laporanList(array $filters = [], ?int $limit = null): string
    {
        // Distabilkan urutan agar hash konsisten
        $normalizedFilters = static::stableArray($filters);

        $keyData = [
            'filters' => $normalizedFilters,
            'limit'   => $limit,
        ];

        return 'laporan_list_' . md5(json_encode($keyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function getPenilaianStats(int $laporanId, bool $filterByUserUnit): string
    {
        return "penilaian_stats:laporan:{$laporanId}:filter_by_user_unit:" . ($filterByUserUnit ? 'yes' : 'no');
    }

    public static function imutLaporansForUnitKerjas(array $unitKerjaIds): string
    {
        sort($unitKerjaIds);
        $joinedIds = implode('_', $unitKerjaIds);
        return "imut_laporans_unit_kerjas_{$joinedIds}";
    }

    public static function imutChartSeriesData(int $laporanId): string
    {
        return "imut:chart-series-data:laporan:{$laporanId}";
    }

    public static function imutDataNavigationBadgeCount(): string
    {
        return 'navigation:imut_data:count';
    }

    public static function statsForUnitKerja(int $userId): string
    {
        return "stats:unit_kerja:user:{$userId}";
    }

    public static function folderPathByFolderId(int $folderId): string
    {
        return "folder:path:folder:{$folderId}";
    }

    public static function folderPathByCollection(string $collection): string
    {
        return "folder:path:collection:{$collection}";
    }

    public static function imutDataNavigationBadgeCountForUser(int $userId): string
    {
        return "navigation:imut_data:count:user:{$userId}";
    }

    /**
     * Cache key untuk statistik kelengkapan unit kerja
     */
    public static function unitKerjaCompletionStats(int $laporanId): string
    {
        return "laporan:unit_kerja_completion:laporan:{$laporanId}";
    }

    /**
     * Cache key untuk statistik kelengkapan indikator mutu
     */
    public static function imutDataCompletionStats(int $laporanId): string
    {
        return "laporan:imut_data_completion:laporan:{$laporanId}";
    }

    /**
     * Cache key untuk widget rekomendasi analisis Tim Mutu - ongoing reports
     */
    public static function recommendationAnalysisTimMutuOngoing(): string
    {
        return 'widget:recommendation_analysis:tim_mutu:ongoing_reports';
    }

    /**
     * Cache key untuk widget rekomendasi analisis Tim Mutu - previous report
     */
    public static function recommendationAnalysisTimMutuPrevious(): string
    {
        return 'widget:recommendation_analysis:tim_mutu:previous_report';
    }

    /**
     * Cache key untuk widget rekomendasi analisis Unit Kerja - ongoing reports
     */
    public static function recommendationAnalysisUnitKerjaOngoing(int $userId): string
    {
        return "widget:recommendation_analysis:unit_kerja:ongoing_reports:user:{$userId}";
    }

    /**
     * Cache key untuk widget rekomendasi analisis Unit Kerja - previous report
     */
    public static function recommendationAnalysisUnitKerjaPrevious(int $userId): string
    {
        return "widget:recommendation_analysis:unit_kerja:previous_report:user:{$userId}";
    }

    /**
     * Cache key untuk completion stats laporan
     */
    public static function recommendationAnalysisCompletionStats(int $laporanId): string
    {
        return "widget:recommendation_analysis:completion_stats:laporan:{$laporanId}";
    }

    /**
     * Cache key untuk completion stats laporan per unit kerja
     */
    public static function recommendationAnalysisCompletionStatsUnitKerja(int $laporanId, int $unitKerjaId): string
    {
        return "widget:recommendation_analysis:completion_stats:laporan:{$laporanId}:unit:{$unitKerjaId}";
    }

    /**
     * Cache key untuk user has unit kerja
     */
    public static function userHasUnitKerja(int $userId): string
    {
        return "user:{$userId}:has_unit_kerja";
    }

    /**
     * Cache key untuk user has unit kerja plus check existence (digunakan untuk cache hasil pengecekan eksistensi unit kerja user, agar tidak perlu query berulang)
     */
    public static function userHasUnitKerjaIds(int $userId): string
    {
        return "user:{$userId}:has_unit_kerja_ids";
    }

    /**
     * Cache key untuk form template data (Form Builder)
     */
    public static function formTemplateData(int $templateId): string
    {
        return "form:template:data:{$templateId}";
    }

    /**
     * Utility: menormalkan array (sort rekursif) supaya JSON/hash stabil.
     */
    private static function stableArray(array $array): array
    {
        ksort($array);
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = static::stableArray($v);
            }
        }
        return $array;
    }
}
