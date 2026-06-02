<?php

namespace App\Repositories\DailyReport;

use App\Models\FormTemplate;
use App\QueryBuilders\FormTemplateQueryBuilder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Repository for FormTemplate data access
 * 
 * Handles all database queries and caching logic.
 * Provides a clean abstraction over Eloquent.
 */
class FormTemplateRepository
{
    // Cache TTL: 5 minutes
    private const CACHE_TTL = 300;

    public function __construct(
        private readonly FormTemplateQueryBuilder $queryBuilder
    ) {
    }

    /**
     * Get monitoring templates with counts for a specific period
     * 
     * @param array $unitKerjaIds
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return Collection
     */
    public function getMonitoringTemplatesForPeriod(
        array $unitKerjaIds,
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        $cacheKey = $this->getCacheKey('monitoring_templates', $unitKerjaIds, $periodStart->format('Y-m'));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($unitKerjaIds, $periodStart, $periodEnd) {
            return $this->queryBuilder
                ->reset()
                ->forUnitKerjas($unitKerjaIds)
                ->validAt(now())
                ->monthlyOnly()
                ->activeOnly()
                ->buildMonitoringQuery()
                ->with(['imutProfile.imutData.categories'])
                ->withCount(['dailyReportResponses as response_count' => function ($query) use ($periodStart, $periodEnd, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$periodStart, $periodEnd]);
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }])
                ->get();
        });
    }

    /**
     * Get all monitoring templates (without period filtering)
     * 
     * @param array $unitKerjaIds
     * @return Collection
     */
    public function getMonitoringTemplates(array $unitKerjaIds): Collection
    {
        $cacheKey = $this->getCacheKey('monitoring_templates_all', $unitKerjaIds);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($unitKerjaIds) {
            return $this->queryBuilder
                ->reset()
                ->forUnitKerjas($unitKerjaIds)
                ->validAt(now())
                ->monthlyOnly()
                ->activeOnly()
                ->buildMonitoringQuery()
                ->with(['imutProfile.imutData.categories'])
                ->get();
        });
    }

    /**
     * Get active templates for user's units
     * 
     * @param array $unitKerjaIds
     * @return Collection
     */
    public function getActiveTemplates(array $unitKerjaIds): Collection
    {
        return $this->queryBuilder
            ->reset()
            ->forUnitKerjas($unitKerjaIds)
            ->validAt(now())
            ->activeOnly()
            ->buildMonitoringQuery()
            ->get();
    }

    /**
     * Get templates by unit kerja (no cache)
     * 
     * @param array $unitKerjaIds
     * @return Collection
     */
    public function getByUnitKerjas(array $unitKerjaIds): Collection
    {
        return $this->queryBuilder
            ->reset()
            ->forUnitKerjas($unitKerjaIds)
            ->buildMonitoringQuery()
            ->get();
    }

    /**
     * Count monitoring templates
     * 
     * @param array $unitKerjaIds
     * @return int
     */
    public function countMonitoring(array $unitKerjaIds): int
    {
        return $this->queryBuilder
            ->reset()
            ->forUnitKerjas($unitKerjaIds)
            ->validAt(now())
            ->monthlyOnly()
            ->activeOnly()
            ->buildMonitoringQuery()
            ->count();
    }

    /**
     * Get single template by ID with relationships
     * 
     * @param int $templateId
     * @return FormTemplate|null
     */
    public function getById(int $templateId): ?FormTemplate
    {
        return FormTemplate::with([
            'imutProfile.imutData.categories',
            'formFields.options'
        ])->find($templateId);
    }

    /**
     * Check if template exists and is active
     * 
     * @param int $templateId
     * @return bool
     */
    public function isActive(int $templateId): bool
    {
        return FormTemplate::where('id', $templateId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Clear cache for monitoring templates
     * 
     * @param array $unitKerjaIds
     */
    public function clearMonitoringCache(array $unitKerjaIds): void
    {
        $month = now()->format('Y-m');
        Cache::forget($this->getCacheKey('monitoring_templates', $unitKerjaIds, $month));
        Cache::forget($this->getCacheKey('monitoring_templates_all', $unitKerjaIds));
    }

    /**
     * Generate cache key for monitoring data
     */
    private function getCacheKey(string $prefix, array $unitKerjaIds, ?string $suffix = null): string
    {
        sort($unitKerjaIds);
        $unitKerjaHash = md5(implode(',', $unitKerjaIds));
        $key = "{$prefix}:{$unitKerjaHash}";
        
        if ($suffix) {
            $key .= ":{$suffix}";
        }
        
        return $key;
    }
}
