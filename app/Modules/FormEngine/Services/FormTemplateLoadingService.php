<?php

namespace App\Modules\FormEngine\Services;

use App\Modules\FormEngine\Models\FormTemplate;
use Illuminate\Support\Collection;

/**
 * Centralized service for loading FormTemplates with consistent eager loading strategy.
 * 
 * Eliminates redundant FormTemplate queries across services by:
 * 1. Providing unified eager loading with all necessary relations
 * 2. Request-scoped caching to avoid reloading same templates
 * 3. Consistent loading strategy across all consumers
 * 
 * Usage:
 *   $template = FormTemplateLoadingService::getTemplate($id);
 *   $templates = FormTemplateLoadingService::getTemplatesByIds($ids);
 *   $templates = FormTemplateLoadingService::getActiveTemplatesForUnitKerjas($unitKerjaIds);
 */
class FormTemplateLoadingService
{
    /**
     * Request-scoped cache for templates
     * Key: "template_{templateId}"
     * 
     * @var array<string, FormTemplate>
     */
    private static array $templateCache = [];

    /**
     * Request-scoped cache for template collections
     * Key: hash of conditions
     * 
     * @var array<string, Collection>
     */
    private static array $collectionCache = [];

    /**
     * Get standard eager loading relations for templates
     * Returns array of relations to eager load
     * 
     * @return array
     */
    private static function getDefaultRelations(): array
    {
        return [
            'imutProfile' => function ($query) {
                $query->with(['imutData' => function ($q) {
                    $q->with(['unitKerja', 'profiles' => function ($pq) {
                        $pq->with('formTemplates');
                    }]);
                }]);
            },
            'formFields' => function ($query) {
                $query->with('options');
            },
        ];
    }

    /**
     * Get single template by ID with standard eager loading
     * Caches result for entire request
     * 
     * @param int $templateId
     * @return FormTemplate|null
     */
    public static function getTemplate(int $templateId): ?FormTemplate
    {
        $cacheKey = "template_{$templateId}";

        if (isset(self::$templateCache[$cacheKey])) {
            return self::$templateCache[$cacheKey];
        }

        $template = FormTemplate::with(self::getDefaultRelations())->find($templateId);

        if ($template) {
            self::$templateCache[$cacheKey] = $template;
        }

        return $template;
    }

    /**
     * Get multiple templates by IDs with standard eager loading
     * Caches each result individually for request
     * 
     * @param array<int> $templateIds
     * @return Collection<FormTemplate>
     */
    public static function getTemplatesByIds(array $templateIds): Collection
    {
        if (empty($templateIds)) {
            return collect();
        }

        // Build cache key from sorted IDs
        sort($templateIds);
        $cacheKey = 'templates_' . md5(implode(',', $templateIds));

        if (isset(self::$collectionCache[$cacheKey])) {
            return self::$collectionCache[$cacheKey];
        }

        $templates = FormTemplate::whereIn('id', $templateIds)
            ->with(self::getDefaultRelations())
            ->get();

        // Also cache individual templates
        foreach ($templates as $template) {
            self::$templateCache["template_{$template->id}"] = $template;
        }

        self::$collectionCache[$cacheKey] = $templates;

        return $templates;
    }

    /**
     * Get active templates for user's unit kerjas
     * Used by DailyReportMonitoringService and similar
     * 
     * @param array<int> $unitKerjaIds
     * @param \DateTime|null $validDate Date to check validity (defaults to now)
     * @return Collection<FormTemplate>
     */
    public static function getActiveTemplatesForUnitKerjas(array $unitKerjaIds, ?\DateTime $validDate = null): Collection
    {
        if (empty($unitKerjaIds)) {
            return collect();
        }

        $validDate = $validDate ?? now();
        $dateStr = $validDate->format('Y-m-d');
        
        // Build cache key from sorted unit_kerja IDs and date
        sort($unitKerjaIds);
        $cacheKey = 'templates_units_' . md5(implode(',', $unitKerjaIds) . '_' . $dateStr);

        if (isset(self::$collectionCache[$cacheKey])) {
            return self::$collectionCache[$cacheKey];
        }

        $templates = FormTemplate::whereHas('imutProfile.imutData.unitKerja', function ($q) use ($unitKerjaIds) {
            $q->whereIn('unit_kerja_id', $unitKerjaIds);
        })
        ->whereHas('imutProfile', function ($q) use ($validDate) {
            $q->where('valid_from', '<=', $validDate)
                ->where(function ($subQ) use ($validDate) {
                    $subQ->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', $validDate);
                });
        })
        ->with(self::getDefaultRelations())
        ->get();

        self::$collectionCache[$cacheKey] = $templates;

        return $templates;
    }

    /**
     * Get templates by imutProfile (indicator)
     * Used when you have the profile already and just need templates
     * 
     * @param array<int> $profileIds
     * @return Collection<FormTemplate>
     */
    public static function getTemplatesByProfileIds(array $profileIds): Collection
    {
        if (empty($profileIds)) {
            return collect();
        }

        sort($profileIds);
        $cacheKey = 'templates_profiles_' . md5(implode(',', $profileIds));

        if (isset(self::$collectionCache[$cacheKey])) {
            return self::$collectionCache[$cacheKey];
        }

        $templates = FormTemplate::whereIn('imut_profil_id', $profileIds)
            ->with(self::getDefaultRelations())
            ->get();

        // Also cache individual templates
        foreach ($templates as $template) {
            self::$templateCache["template_{$template->id}"] = $template;
        }

        self::$collectionCache[$cacheKey] = $templates;

        return $templates;
    }

    /**
     * Clear all caches (useful for testing or manual invalidation)
     */
    public static function clearCache(): void
    {
        self::$templateCache = [];
        self::$collectionCache = [];
    }

    /**
     * Clear single template from cache
     */
    public static function clearTemplateFromCache(int $templateId): void
    {
        unset(self::$templateCache["template_{$templateId}"]);
    }
}
