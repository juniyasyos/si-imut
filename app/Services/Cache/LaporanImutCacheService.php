<?php

namespace App\Services\Cache;

use App\Models\LaporanImut;
use App\Models\ImutData;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Collection;

/**
 * Laporan IMUT Cache Service
 *
 * Handles caching for laporan IMUT related data including:
 * - Report lists and pagination
 * - Assessment period lookups
 * - Unit kerja statistics
 * - Report calculations and aggregations
 */
class LaporanImutCacheService extends BaseCacheService
{
    /**
     * Cache TTL for different data types
     */
    protected const REPORT_LIST_TTL = 1800; // 30 minutes
    protected const REPORT_DETAIL_TTL = 3600; // 1 hour
    protected const STATISTICS_TTL = 900; // 15 minutes
    protected const CALCULATIONS_TTL = 7200; // 2 hours

    protected function getKeyPrefix(): string
    {
        return 'laporan_imut';
    }

    protected function getCacheTags(): array
    {
        return ['laporan_imut', 'reports', 'statistics'];
    }

    /**
     * Cache laporan list with pagination and filters
     */
    public function getLaporanList(array $filters = [], int $perPage = 15, int $page = 1): Collection
    {
        $filterKey = md5(serialize($filters));
        $key = "list:{$filterKey}:page_{$page}:per_{$perPage}";

        return $this->remember($key, function () use ($filters, $perPage, $page) {
            $query = LaporanImut::query();

            // Apply filters
            if (isset($filters['assessment_period'])) {
                $query->where('assessment_period', 'like', '%' . $filters['assessment_period'] . '%');
            }

            if (isset($filters['unit_kerja_id'])) {
                $query->whereHas('imutData', function ($q) use ($filters) {
                    $q->where('unit_kerja_id', $filters['unit_kerja_id']);
                });
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            return $query->orderByDesc('assessment_period_start')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->with(['imutData.unitKerja'])
                ->get();
        }, self::REPORT_LIST_TTL);
    }

    /**
     * Cache individual laporan with full relations
     */
    public function getLaporanDetail(int $laporanId): ?LaporanImut
    {
        $key = "detail:{$laporanId}";

        return $this->remember($key, function () use ($laporanId) {
            return LaporanImut::with([
                'imutData.unitKerja',
                'imutData.profiles.category',
                'imutPenilaian'
            ])->find($laporanId);
        }, self::REPORT_DETAIL_TTL);
    }

    /**
     * Cache laporan statistics by period
     */
    public function getStatisticsByPeriod(string $period): array
    {
        $key = "statistics:period:{$period}";

        return $this->remember($key, function () use ($period) {
            $laporans = LaporanImut::where('assessment_period', $period)
                ->with(['imutData.unitKerja'])
                ->get();

            return [
                'total_reports' => $laporans->count(),
                'completed_reports' => $laporans->where('status', 'completed')->count(),
                'pending_reports' => $laporans->where('status', 'pending')->count(),
                'unit_kerja_count' => $laporans->pluck('imutData.unit_kerja_id')->unique()->count(),
                'average_score' => $laporans->avg('total_score'),
                'top_performers' => $laporans->sortByDesc('total_score')->take(5)->values(),
                'period' => $period
            ];
        }, self::STATISTICS_TTL);
    }

    /**
     * Cache unit kerja performance data
     */
    public function getUnitKerjaPerformance(int $unitKerjaId, ?string $period = null): array
    {
        $periodKey = $period ? "period_{$period}" : 'all_periods';
        $key = "unit_performance:{$unitKerjaId}:{$periodKey}";

        return $this->remember($key, function () use ($unitKerjaId, $period) {
            $query = LaporanImut::whereHas('imutData', function ($q) use ($unitKerjaId) {
                $q->where('unit_kerja_id', $unitKerjaId);
            });

            if ($period) {
                $query->where('assessment_period', $period);
            }

            $laporans = $query->with(['imutData.profiles'])->get();

            return [
                'unit_kerja_id' => $unitKerjaId,
                'period' => $period,
                'total_reports' => $laporans->count(),
                'average_score' => $laporans->avg('total_score'),
                'best_score' => $laporans->max('total_score'),
                'worst_score' => $laporans->min('total_score'),
                'score_trend' => $laporans->sortBy('assessment_period_start')
                    ->pluck('total_score', 'assessment_period')
                    ->toArray(),
                'category_breakdown' => $this->calculateCategoryBreakdown($laporans)
            ];
        }, self::STATISTICS_TTL);
    }

    /**
     * Cache assessment period options
     */
    public function getAssessmentPeriods(): array
    {
        $key = 'assessment_periods';

        return $this->remember($key, function () {
            return LaporanImut::distinct()
                ->pluck('assessment_period')
                ->sort()
                ->values()
                ->toArray();
        }, self::REPORT_LIST_TTL);
    }

    /**
     * Cache dashboard summary data
     */
    public function getDashboardSummary(?string $period = null): array
    {
        $periodKey = $period ? "period_{$period}" : 'current';
        $key = "dashboard_summary:{$periodKey}";

        return $this->remember($key, function () use ($period) {
            $query = LaporanImut::query();

            if ($period) {
                $query->where('assessment_period', $period);
            } else {
                // Default to current year
                $query->whereYear('assessment_period_start', now()->year);
            }

            $laporans = $query->with(['unitKerjas'])->get();

            return [
                'total_reports' => $laporans->count(),
                'completed_reports' => $laporans->where('status', 'completed')->count(),
                'pending_reports' => $laporans->where('status', 'pending')->count(),
                'average_score' => round($laporans->avg('total_score'), 2),
                'score_distribution' => $this->calculateScoreDistribution($laporans),
                'top_unit_kerja' => $this->getTopUnitKerja($laporans),
                'recent_reports' => $laporans->sortByDesc('updated_at')->take(5)->values(),
                'period' => $period ?? 'current_year',
                'last_updated' => now()->toISOString()
            ];
        }, self::STATISTICS_TTL);
    }

    /**
     * Invalidate cache for specific laporan
     */
    public function invalidateLaporan(int $laporanId): void
    {
        $this->forget("detail:{$laporanId}");

        // Also invalidate related caches
        $laporan = LaporanImut::find($laporanId);
        if ($laporan) {
            $period = $laporan->assessment_period_start->format('Y-m');
            $this->invalidateStatistics($period);
        }
    }

    /**
     * Invalidate statistics for a specific period
     */
    public function invalidateStatistics(string $period): void
    {
        $this->forget("statistics:period:{$period}");
        $this->forget("dashboard_summary:period_{$period}");
        $this->forget("dashboard_summary:current");
    }

    /**
     * Invalidate unit kerja related caches
     */
    public function invalidateUnitKerjaCache(?int $unitKerjaId): void
    {
        if (!$unitKerjaId) return;

        // Clear performance data
        $periodKey = 'all_periods';
        $this->forget("unit_performance:{$unitKerjaId}:{$periodKey}");

        // Clear period-specific performance data (we'd need to know periods to be more specific)
        // For now, we'll use a broader invalidation approach
    }

    /**
     * Invalidate all list caches
     */
    public function invalidateListCaches(): void
    {
        $this->forget('assessment_periods');
        // List caches use dynamic keys, so we rely on TTL for those
    }

    /**
     * Calculate category breakdown for reports
     */
    private function calculateCategoryBreakdown(Collection $laporans): array
    {
        $breakdown = [];

        foreach ($laporans as $laporan) {
            foreach ($laporan->imutData->profiles ?? [] as $profile) {
                $categoryName = $profile->category->name ?? 'Unknown';

                if (!isset($breakdown[$categoryName])) {
                    $breakdown[$categoryName] = [
                        'count' => 0,
                        'total_score' => 0,
                        'average_score' => 0
                    ];
                }

                $breakdown[$categoryName]['count']++;
                $breakdown[$categoryName]['total_score'] += $profile->score ?? 0;
                $breakdown[$categoryName]['average_score'] =
                    $breakdown[$categoryName]['total_score'] / $breakdown[$categoryName]['count'];
            }
        }

        return $breakdown;
    }

    /**
     * Calculate score distribution
     */
    private function calculateScoreDistribution(Collection $laporans): array
    {
        $distribution = [
            'excellent' => 0, // 90-100
            'good' => 0,      // 80-89
            'fair' => 0,      // 70-79
            'poor' => 0       // <70
        ];

        foreach ($laporans as $laporan) {
            $score = $laporan->total_score ?? 0;

            if ($score >= 90) {
                $distribution['excellent']++;
            } elseif ($score >= 80) {
                $distribution['good']++;
            } elseif ($score >= 70) {
                $distribution['fair']++;
            } else {
                $distribution['poor']++;
            }
        }

        return $distribution;
    }

    /**
     * Get top performing unit kerja
     */
    private function getTopUnitKerja(Collection $laporans): array
    {
        return $laporans
            ->groupBy('imutData.unit_kerja_id')
            ->map(function ($group) {
                $unitKerja = $group->first()->imutData->unitKerja ?? null;
                return [
                    'unit_kerja_id' => $unitKerja?->id,
                    'unit_kerja_name' => $unitKerja?->name,
                    'report_count' => $group->count(),
                    'average_score' => round($group->avg('total_score'), 2),
                    'best_score' => $group->max('total_score')
                ];
            })
            ->sortByDesc('average_score')
            ->take(5)
            ->values()
            ->toArray();
    }
}
