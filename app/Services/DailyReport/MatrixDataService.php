<?php

namespace App\Services\DailyReport;

use App\Models\User;
use App\Models\FormTemplate;
use App\Support\CacheKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\DailyReport\CachedSettingsService;

class MatrixDataService
{
    // Cache TTL: 5 minutes for matrix data
    const CACHE_TTL = 300;

    /**
     * In-memory cache to avoid repeated unit kerja lookups in one request.
     *
     * @var array<int, array<int, int>>
     */
    private array $userUnitKerjaIdsCache = [];

    /**
     * In-memory cache for matrix indicators per request.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $indicatorsCache = [];

    /**
     * In-memory cache for compliance summaries per request.
     *
     * @var array<string, \Illuminate\Support\Collection>
     */
    private array $complianceSummariesCache = [];

    /**
     * Load matrix metadata only (indicators + daysWithData map)
     * Fast, lightweight - used for sidebar & initial render
     */
    public function loadMatrixMetadata(string $selectedMonth): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['indicators' => [], 'daysWithData' => [], 'daysInMonth' => []];
        }

        $cacheKey = "matrix_metadata_{$user->id}_{$selectedMonth}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($selectedMonth, $user) {
            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            if (empty($unitKerjaIds)) {
                return ['indicators' => [], 'daysWithData' => [], 'daysInMonth' => []];
            }

            // Get indicators
            $indicators = $this->getIndicators($unitKerjaIds);

            // Calculate days in selected month
            $date = Carbon::parse($selectedMonth . '-01');
            $daysInMonth = range(1, $date->daysInMonth);

            // Get compliance summaries (for hasAnyData check only)
            $complianceSummaries = $this->getComplianceSummaries($unitKerjaIds, $date);

            // Build daysWithData map (fast, no heavy computation)
            $daysWithData = [];
            foreach ($daysInMonth as $day) {
                $dateStr = $date->copy()->day($day)->format('Y-m-d');
                $hasData = false;
                
                // Check if ANY indicator has data for this day
                foreach ($indicators as $indicator) {
                    if ($complianceSummaries->get($indicator['id'])?->get($dateStr)) {
                        $hasData = true;
                        break;
                    }
                }
                $daysWithData[$day] = $hasData;
            }

            return [
                'indicators' => $indicators,
                'daysWithData' => $daysWithData,
                'daysInMonth' => $daysInMonth
            ];
        });
    }

    /**
     * Load full matrix data for selected month
     * Heavy computation - cached, callable via Livewire
     */
    public function loadFullMatrixData(string $selectedMonth): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['matrixData' => []];
        }

        $cacheKey = "matrix_full_{$user->id}_{$selectedMonth}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($selectedMonth, $user) {
            $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

            if (empty($unitKerjaIds)) {
                return ['matrixData' => []];
            }

            $backDays = CachedSettingsService::getBackDataEntryDays();

            // Get indicators & days
            $indicators = $this->getIndicators($unitKerjaIds);
            $date = Carbon::parse($selectedMonth . '-01');
            $daysInMonth = range(1, $date->daysInMonth);

            // Get compliance summaries
            $complianceSummaries = $this->getComplianceSummaries($unitKerjaIds, $date);

            // Build matrix data
            $matrixData = $this->buildMatrixData($indicators, $daysInMonth, $date, $complianceSummaries, $backDays);

            return ['matrixData' => $matrixData];
        });
    }

    /**
     * Load full matrix data (legacy method for backward compatibility)
     */
    public function loadMatrixData(string $selectedMonth): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['indicators' => [], 'matrixData' => [], 'daysInMonth' => []];
        }

        $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);

        if (empty($unitKerjaIds)) {
            return ['indicators' => [], 'matrixData' => [], 'daysInMonth' => []];
        }

        $backDays = CachedSettingsService::getBackDataEntryDays();

        // Get indicators
        $indicators = $this->getIndicators($unitKerjaIds);

        // Calculate days in selected month
        $date = Carbon::parse($selectedMonth . '-01');
        $daysInMonth = range(1, $date->daysInMonth);

        // Get compliance summaries
        $complianceSummaries = $this->getComplianceSummaries($unitKerjaIds, $date);

        // Build matrix data
        $matrixData = $this->buildMatrixData($indicators, $daysInMonth, $date, $complianceSummaries, $backDays);

        return [
            'indicators' => $indicators,
            'matrixData' => $matrixData,
            'daysInMonth' => $daysInMonth
        ];
    }

    /**
     * Get indicators for user's units (active templates only)
     * Using optimized Eloquent queries with proper relationship loading
     */
    private function getIndicators(array $unitKerjaIds): array
    {
        $cacheKey = $this->getMatrixQueryCacheKey($unitKerjaIds);

        if (isset($this->indicatorsCache[$cacheKey])) {
            return $this->indicatorsCache[$cacheKey];
        }

        $formTemplates = FormTemplate::forUserUnitKerjas($unitKerjaIds)
            ->monthlyIndicators()
            ->activeForCurrentDate()
            ->with([
                'imutProfile' => fn($q) => $q->select('id', 'version', 'imut_data_id'),
                'imutProfile.imutData' => fn($q) => $q->select('id', 'title', 'imut_kategori_id'),
                'imutProfile.imutData.categories' => fn($q) => $q->select('id', 'category_name'),
            ])
            ->select(
                'form_templates.id',
                'form_templates.title',
                'form_templates.imut_profile_id'
            )
            ->distinct()
            ->get();

        return $this->indicatorsCache[$cacheKey] = $formTemplates->map(function ($formTemplate) {
            return [
                'id' => $formTemplate->id,
                'title' => $formTemplate->imutProfile->imutData->title,
                'category' => $formTemplate->imutProfile->imutData->categories->category_name,
                'category_id' => $formTemplate->imutProfile->imutData->imut_kategori_id,
                'imut_profile_version' => $formTemplate->imutProfile->version,
            ];
        })->toArray();
    }

    /**
     * Get compliance summaries for the month
     * Optimized query structure with efficient grouping
     */
    private function getComplianceSummaries(array $unitKerjaIds, Carbon $date): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getMatrixQueryCacheKey($unitKerjaIds, $date->format('Y-m'));

        if (isset($this->complianceSummariesCache[$cacheKey])) {
            return $this->complianceSummariesCache[$cacheKey];
        }

        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');
        $now = now();

        $summaries = \App\Models\DailyReportResponse::select([
            'form_templates.id as form_template_id',
            DB::raw('DATE(daily_report_responses.report_date) as report_date'),
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count')
        ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->whereHas('formTemplate.imutProfile', function ($q) use ($now) {
                // Validate profile is currently valid
                $q->where('valid_from', '<=', $now)
                  ->where(function ($subQ) use ($now) {
                      $subQ->whereNull('valid_until')
                           ->orWhere('valid_until', '>=', $now);
                  });
            })
            ->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds)
            ->whereBetween('daily_report_responses.report_date', [$startDate, $endDate])
            ->groupBy('form_templates.id', DB::raw('DATE(daily_report_responses.report_date)'))
            ->get()
            ->groupBy('form_template_id')
            ->map(function ($dates) {
                // key by plain Y-m-d string to match buildMatrixData lookup
                return $dates->keyBy(function ($item) {
                    return Carbon::parse($item->report_date)->format('Y-m-d');
                });
            });

        return $this->complianceSummariesCache[$cacheKey] = $summaries;
    }

    /**
     * Build matrix data array
     */
    private function buildMatrixData(array $indicators, array $daysInMonth, Carbon $date, $complianceSummaries, int $backDays = 6): array
    {
        $matrixData = [];
        
        $today = now()->startOfDay();
        $sixDaysAgo = $today->copy()->subDays($backDays)->startOfDay();

        foreach ($indicators as $indicator) {
            foreach ($daysInMonth as $day) {
                $dateStr = $date->copy()->day($day)->format('Y-m-d');
                $summary = $complianceSummaries->get($indicator['id'])?->get($dateStr);

                $totalCount = $summary ? $summary->total_count : 0;
                $compliantCount = $summary ? $summary->compliant_count : 0;
                $compliancePercentage = $totalCount > 0 ? round(($compliantCount / $totalCount) * 100, 1) : 0;

                $cellDate = $date->copy()->day($day)->startOfDay();

                // debugging: log summary for today to confirm counts
                // if ($today->isSameDay($cellDate)) {
                //     dd('buildMatrixData today', [
                //         'dateStr' => $dateStr,
                //         'indicatorId' => $indicator['id'],
                //         'indicatorTitle' => $indicator['title'],
                //         'summary' => $summary,
                //         'totalCount' => $totalCount,
                //         'compliantCount' => $compliantCount,
                //         'compliancePercentage' => $compliancePercentage,
                //         'complianceSummaries' => $complianceSummaries->get($indicator['id']),
                //     ]);
                // }

                $cellState = 'disabled';
                if ($cellDate->lte($today)) {
                    $isWithinWindow = $cellDate->gte($sixDaysAgo);
                    if ($totalCount > 0) {
                        // Has data: distinguishes between editable (within window) and locked (outside window)
                        $cellState = $isWithinWindow ? 'done' : 'done_locked';
                    } elseif ($isWithinWindow) {
                        $cellState = 'pending';
                    } else {
                        $cellState = 'overdue';
                    }
                }

                $summaryData = null;
                if ($totalCount > 0) {
                    $summaryData = [
                        'count' => $totalCount,
                        'numerator' => $compliantCount,
                        'denominator' => $totalCount,
                        'percentage' => $compliancePercentage,
                    ];
                }

                $matrixData[$indicator['id']][$day] = [
                    'date' => $dateStr,
                    'has_data' => $totalCount > 0,
                    'count' => $totalCount,
                    'compliance_percentage' => $compliancePercentage,
                    'compliance_count' => $compliantCount,
                    'total_count' => $totalCount,
                    'cell_state' => $cellState,
                    'summary' => $summaryData,
                    'is_today' => $cellDate->isToday(),
                ];
            }
        }

        return $matrixData;
    }

    /**
     * Get real indicator status from database
     * Optimized to avoid repeated database queries for settings
     */
    public function getRealIndicatorStatus(int $indicatorId, string $date): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['error' => 'User not authenticated', 'status' => 'error', 'count' => 0];
        }

        $unitKerjaIds = $this->getUserUnitKerjaIds($user->id);
        if (empty($unitKerjaIds)) {
            return ['error' => 'No unit kerja found', 'status' => 'error', 'count' => 0];
        }

        $reports = \App\Models\DailyReportResponse::select([
            'daily_report_responses.id',
            'daily_report_responses.report_date',
            'daily_report_responses.compliance_status',
            'daily_report_responses.total_score',
            'daily_report_responses.created_at'
        ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->where('form_templates.id', $indicatorId)
            ->whereIn('daily_report_responses.unit_kerja_id', $unitKerjaIds)
            ->whereDate('daily_report_responses.report_date', $date)
            ->get();

        $count = $reports->count();
        $cellDate = Carbon::parse($date)->startOfDay();
        $today = now()->startOfDay();
        
        // Get cached setting instead of querying repeatedly
        $backDays = CachedSettingsService::getBackDataEntryDays();
        $sixDaysAgo = $today->copy()->subDays($backDays)->startOfDay();

        if ($count > 0) {
            $status = 'done';
        } elseif ($cellDate->lte($today) && $cellDate->gte($sixDaysAgo)) {
            $status = 'pending';
        } elseif ($cellDate->lt($sixDaysAgo)) {
            $status = 'overdue';
        } else {
            $status = 'disabled';
        }

        return [
            'status' => $status,
            'count' => $count,
            'reports' => $reports->toArray(),
            'date' => $date
        ];
    }

    /**
     * Get user's unit kerja IDs with caching
     * Cache for 1 hour or until invalidated
     * Uses CacheKey::userHasUnitKerjaIds for consistent cache key
     */
    private function getUserUnitKerjaIds(int $userId): array
    {
        if (isset($this->userUnitKerjaIdsCache[$userId])) {
            return $this->userUnitKerjaIdsCache[$userId];
        }

        $ids = Cache::remember(
            CacheKey::userHasUnitKerjaIds($userId),
            3600,
            function () use ($userId) {
                $user = User::query()->with('unitKerjas:id')->find($userId);

                return $user?->unitKerjas?->pluck('id')->all() ?? [];
            }
        );

        return $this->userUnitKerjaIdsCache[$userId] = $ids;
    }

    /**
     * Build a stable in-memory cache key for matrix queries.
     */
    private function getMatrixQueryCacheKey(array $unitKerjaIds, ?string $suffix = null): string
    {
        sort($unitKerjaIds);
        $key = md5(implode(',', $unitKerjaIds));

        if ($suffix !== null) {
            $key .= ':' . $suffix;
        }

        return $key;
    }
}
