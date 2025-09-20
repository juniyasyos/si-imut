<?php

namespace App\Services\Cache;

use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Collection;

/**
 * IMUT Data Cache Service
 *
 * Handles caching for IMUT data including:
 * - IMUT data lists and filters
 * - Profile data and calculations
 * - Unit kerja aggregations
 * - Performance metrics
 */
class ImutDataCacheService extends BaseCacheService
{
    protected const DATA_LIST_TTL = 1800; // 30 minutes
    protected const DATA_DETAIL_TTL = 3600; // 1 hour
    protected const METRICS_TTL = 900; // 15 minutes
    protected const AGGREGATIONS_TTL = 7200; // 2 hours

    protected function getKeyPrefix(): string
    {
        return 'imut_data';
    }

    protected function getCacheTags(): array
    {
        return ['imut_data', 'profiles', 'metrics'];
    }

    /**
     * Cache IMUT data list with filters
     */
    public function getImutDataList(array $filters = [], int $perPage = 15, int $page = 1): Collection
    {
        $filterKey = md5(serialize($filters));
        $key = "list:{$filterKey}:page_{$page}:per_{$perPage}";

        return $this->remember($key, function () use ($filters, $perPage, $page) {
            $query = ImutData::query();

            // Apply filters
            if (isset($filters['unit_kerja_id'])) {
                $query->where('unit_kerja_id', $filters['unit_kerja_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['period'])) {
                $query->where('period', $filters['period']);
            }

            return $query->with(['unitKerja', 'profiles.category'])
                ->orderByDesc('created_at')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
        }, self::DATA_LIST_TTL);
    }

    /**
     * Cache individual IMUT data with full relations
     */
    public function getImutDataDetail(int $imutDataId): ?ImutData
    {
        $key = "detail:{$imutDataId}";

        return $this->remember($key, function () use ($imutDataId) {
            return ImutData::with([
                'unitKerja',
                'profiles.category',
                'laporan'
            ])->find($imutDataId);
        }, self::DATA_DETAIL_TTL);
    }

    /**
     * Cache unit kerja IMUT overview
     */
    public function getUnitKerjaOverview(int $unitKerjaId): array
    {
        $key = "unit_overview:{$unitKerjaId}";

        return $this->remember($key, function () use ($unitKerjaId) {
            $imutData = ImutData::where('unit_kerja_id', $unitKerjaId)
                ->with(['profiles.category', 'laporan'])
                ->get();

            $unitKerja = UnitKerja::find($unitKerjaId);

            return [
                'unit_kerja' => $unitKerja,
                'total_imut_data' => $imutData->count(),
                'completed_profiles' => $imutData->sum(fn($data) => $data->profiles->count()),
                'pending_assessments' => $imutData->where('status', 'pending')->count(),
                'average_completion_rate' => $this->calculateCompletionRate($imutData),
                'category_distribution' => $this->calculateCategoryDistribution($imutData),
                'recent_activity' => $imutData->sortByDesc('updated_at')->take(5)->values(),
                'performance_trends' => $this->calculatePerformanceTrends($imutData)
            ];
        }, self::METRICS_TTL);
    }

    /**
     * Cache profile statistics by category
     */
    public function getProfileStatsByCategory(int $categoryId): array
    {
        $key = "profile_stats:category:{$categoryId}";

        return $this->remember($key, function () use ($categoryId) {
            $profiles = ImutProfile::whereHas('imutData', function ($q) use ($categoryId) {
                    $q->where('imut_kategori_id', $categoryId);
                })
                ->with(['imutData.unitKerja', 'imutData.categories', 'penilaian'])
                ->get();

            return [
                'category_id' => $categoryId,
                'total_profiles' => $profiles->count(),
                'average_score' => round($profiles->avg(function ($profile) {
                    $penilaian = $profile->penilaian->first();
                    if ($penilaian && $penilaian->denominator_value > 0) {
                        return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                    }
                    return 0;
                }), 2),
                'highest_score' => $profiles->max(function ($profile) {
                    $penilaian = $profile->penilaian->first();
                    if ($penilaian && $penilaian->denominator_value > 0) {
                        return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                    }
                    return 0;
                }),
                'lowest_score' => $profiles->min(function ($profile) {
                    $penilaian = $profile->penilaian->first();
                    if ($penilaian && $penilaian->denominator_value > 0) {
                        return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                    }
                    return 0;
                }),
                'score_distribution' => $this->calculateScoreDistribution($profiles),
                'unit_kerja_performance' => $this->calculateUnitKerjaPerformance($profiles),
                'completion_rate' => $this->calculateProfileCompletionRate($profiles)
            ];
        }, self::METRICS_TTL);
    }

    /**
     * Cache global IMUT metrics
     */
    public function getGlobalMetrics(): array
    {
        $key = 'global_metrics';

        return $this->remember($key, function () {
            $allImutData = ImutData::with(['profiles', 'unitKerja'])->get();
            $allProfiles = ImutProfile::with(['category'])->get();

            return [
                'total_imut_data' => $allImutData->count(),
                'total_profiles' => $allProfiles->count(),
                'total_unit_kerja' => $allImutData->pluck('unit_kerja_id')->unique()->count(),
                'average_profiles_per_unit' => round($allProfiles->count() / max($allImutData->pluck('unit_kerja_id')->unique()->count(), 1), 2),
                'category_breakdown' => $allProfiles->groupBy('imut_category_id')->map->count(),
                'completion_status' => [
                    'completed' => $allImutData->where('status', 'completed')->count(),
                    'pending' => $allImutData->where('status', 'pending')->count(),
                    'in_progress' => $allImutData->where('status', 'in_progress')->count()
                ],
                'quality_metrics' => $this->calculateQualityMetrics($allProfiles),
                'last_updated' => now()->toISOString()
            ];
        }, self::AGGREGATIONS_TTL);
    }

    /**
     * Cache benchmarking data
     */
    public function getBenchmarkingData(array $filters = []): array
    {
        $filterKey = md5(serialize($filters));
        $key = "benchmarking:{$filterKey}";

        return $this->remember($key, function () use ($filters) {
            $query = ImutData::with(['profiles.penilaian', 'unitKerja', 'categories']);

            // Apply filters
            if (isset($filters['region'])) {
                $query->whereHas('unitKerja', function ($q) use ($filters) {
                    $q->where('region', $filters['region']);
                });
            }

            if (isset($filters['type'])) {
                $query->whereHas('unitKerja', function ($q) use ($filters) {
                    $q->where('type', $filters['type']);
                });
            }

            $imutData = $query->get();

            return [
                'dataset_size' => $imutData->count(),
                'filters_applied' => $filters,
                'performance_rankings' => $this->calculatePerformanceRankings($imutData),
                'peer_comparisons' => $this->calculatePeerComparisons($imutData),
                'best_practices' => $this->identifyBestPractices($imutData),
                'improvement_opportunities' => $this->identifyImprovementOpportunities($imutData)
            ];
        }, self::AGGREGATIONS_TTL);
    }

    /**
     * Invalidate cache for specific IMUT data
     */
    public function invalidateImutData(int $imutDataId): void
    {
        $this->forget("detail:{$imutDataId}");

        // Get the IMUT data to invalidate related caches
        $imutData = ImutData::with('unitKerja')->find($imutDataId);
        if ($imutData) {
            // Invalidate cache for all associated unit kerjas
            foreach ($imutData->unitKerja as $unitKerja) {
                $this->invalidateUnitKerjaCache($unitKerja->id);
            }
            $this->invalidateGlobalCaches();
        }
    }

    /**
     * Invalidate cache for unit kerja
     */
    public function invalidateUnitKerjaCache(int $unitKerjaId): void
    {
        $this->forget("unit_overview:{$unitKerjaId}");
    }

    /**
     * Invalidate cache for category
     */
    public function invalidateCategoryCache(int $categoryId): void
    {
        $this->forget("profile_stats:category:{$categoryId}");
    }

    /**
     * Invalidate global caches
     */
    public function invalidateGlobalCaches(): void
    {
        $this->forget('global_metrics');
        // Benchmarking data uses dynamic keys, so we rely on TTL
    }

    /**
     * Calculate completion rate for IMUT data
     */
    private function calculateCompletionRate(Collection $imutData): float
    {
        if ($imutData->isEmpty()) {
            return 0.0;
        }

        $totalExpectedProfiles = $imutData->count() * 10; // Assuming 10 profiles per IMUT data
        $actualProfiles = $imutData->sum(fn($data) => $data->profiles->count());

        return round(($actualProfiles / $totalExpectedProfiles) * 100, 2);
    }

    /**
     * Calculate category distribution
     */
    private function calculateCategoryDistribution(Collection $imutData): array
    {
        $distribution = [];

        foreach ($imutData as $data) {
            foreach ($data->profiles as $profile) {
                $categoryName = $profile->category->name ?? 'Unknown';
                $distribution[$categoryName] = ($distribution[$categoryName] ?? 0) + 1;
            }
        }

        return $distribution;
    }

    /**
     * Calculate performance trends
     */
    private function calculatePerformanceTrends(Collection $imutData): array
    {
        return $imutData
            ->groupBy('period')
            ->map(function ($group) {
                $averageScore = $group->avg(function ($data) {
                    return $data->profiles->avg(function ($profile) {
                        $penilaian = $profile->penilaian->first();
                        if ($penilaian && $penilaian->denominator_value > 0) {
                            return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                        }
                        return 0;
                    });
                });

                return [
                    'period' => $group->first()->period,
                    'count' => $group->count(),
                    'average_score' => round($averageScore, 2)
                ];
            })
            ->sortBy('period')
            ->values()
            ->toArray();
    }

    /**
     * Calculate score distribution for profiles
     */
    private function calculateScoreDistribution(Collection $profiles): array
    {
        $distribution = [
            'excellent' => 0, // 90-100
            'good' => 0,      // 80-89
            'fair' => 0,      // 70-79
            'poor' => 0       // <70
        ];

        foreach ($profiles as $profile) {
            $score = 0;
            $penilaian = $profile->penilaian->first();
            if ($penilaian && $penilaian->denominator_value > 0) {
                $score = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
            }

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
     * Calculate unit kerja performance for profiles
     */
    private function calculateUnitKerjaPerformance(Collection $profiles): array
    {
        $results = [];

        foreach ($profiles as $profile) {
            // Get all unit kerjas for this profile's imut data
            foreach ($profile->imutData->unitKerja as $unitKerja) {
                $unitKerjaId = $unitKerja->id;

                if (!isset($results[$unitKerjaId])) {
                    $results[$unitKerjaId] = [
                        'unit_kerja_id' => $unitKerjaId,
                        'unit_kerja_name' => $unitKerja->name,
                        'profiles' => [],
                        'scores' => []
                    ];
                }

                $results[$unitKerjaId]['profiles'][] = $profile;

                // Calculate score for this profile
                $penilaian = $profile->penilaian->first();
                if ($penilaian && $penilaian->denominator_value > 0) {
                    $score = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                    $results[$unitKerjaId]['scores'][] = $score;
                }
            }
        }

        // Calculate final metrics for each unit kerja
        return collect($results)->map(function ($data) {
            $scores = $data['scores'];
            return [
                'unit_kerja_id' => $data['unit_kerja_id'],
                'unit_kerja_name' => $data['unit_kerja_name'],
                'profile_count' => count($data['profiles']),
                'average_score' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0,
                'best_score' => count($scores) > 0 ? max($scores) : 0
            ];
        })->sortByDesc('average_score')->values()->toArray();
    }

    /**
     * Calculate profile completion rate
     */
    private function calculateProfileCompletionRate(Collection $profiles): float
    {
        if ($profiles->isEmpty()) {
            return 0.0;
        }

        $completedProfiles = $profiles->filter(function ($profile) {
            return !empty($profile->score) && !empty($profile->description);
        })->count();

        return round(($completedProfiles / $profiles->count()) * 100, 2);
    }

    /**
     * Calculate quality metrics
     */
    private function calculateQualityMetrics(Collection $profiles): array
    {
        $scores = [];
        foreach ($profiles as $profile) {
            foreach ($profile->penilaian as $penilaian) {
                if ($penilaian->numerator > 0 && $penilaian->denominator > 0) {
                    $scores[] = ($penilaian->numerator / $penilaian->denominator) * 100;
                }
            }
        }

        $variance = 0;
        if (count($scores) > 1) {
            $mean = array_sum($scores) / count($scores);
            $variance = array_sum(array_map(function($score) use ($mean) {
                return pow($score - $mean, 2);
            }, $scores)) / count($scores);
        }

        return [
            'data_completeness' => $this->calculateProfileCompletionRate($profiles),
            'score_variance' => round($variance, 2),
            'outlier_count' => $this->countOutliers($profiles),
            'consistency_score' => $this->calculateConsistencyScore($profiles)
        ];
    }

    /**
     * Count outliers in profile scores
     */
    private function countOutliers(Collection $profiles): int
    {
        $scores = $profiles->whereNotNull('score')->pluck('score');
        if ($scores->count() < 4) return 0;

        $q1 = $scores->percentile(25);
        $q3 = $scores->percentile(75);
        $iqr = $q3 - $q1;
        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        return $scores->filter(function ($score) use ($lowerBound, $upperBound) {
            return $score < $lowerBound || $score > $upperBound;
        })->count();
    }

    /**
     * Calculate consistency score
     */
    private function calculateConsistencyScore(Collection $profiles): float
    {
        // This is a simplified consistency metric
        // In a real implementation, you might have more sophisticated algorithms
        $scores = $profiles->whereNotNull('score')->pluck('score');
        if ($scores->count() < 2) return 100.0;

        $variance = $scores->var();
        $maxVariance = 2500; // Maximum expected variance (50^2)

        return round(max(0, 100 - (($variance / $maxVariance) * 100)), 2);
    }

    /**
     * Calculate performance rankings
     */
    private function calculatePerformanceRankings(Collection $imutData): array
    {
        return $imutData
            ->map(function ($data) {
                // Calculate average score from penilaian data if available
                $averageScore = 0;
                $profileCount = $data->profiles->count();

                if ($profileCount > 0) {
                    $totalScore = $data->profiles->sum(function ($profile) {
                        // Calculate score from penilaian data if available
                        $penilaian = $profile->penilaian->first();
                        if ($penilaian && $penilaian->denominator_value > 0) {
                            return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                        }
                        return 0;
                    });
                    $averageScore = $profileCount > 0 ? round($totalScore / $profileCount, 2) : 0;
                }

                return [
                    'unit_kerja_id' => $data->unit_kerja_id ?? null,
                    'unit_kerja_name' => optional($data->unitKerja->first())->name ?? 'Unknown',
                    'average_score' => $averageScore,
                    'profile_count' => $profileCount
                ];
            })
            ->sortByDesc('average_score')
            ->values()
            ->toArray();
    }

    /**
     * Calculate peer comparisons
     */
    private function calculatePeerComparisons(Collection $imutData): array
    {
        // Group by unit kerja type or region for peer comparison
        return $imutData
            ->groupBy(function ($data) {
                return optional($data->unitKerja->first())->type ?? 'Unknown';
            })
            ->map(function ($group, $type) {
                $averageScore = $group->avg(function ($data) {
                    $totalScore = $data->profiles->sum(function ($profile) {
                        $penilaian = $profile->penilaian->first();
                        if ($penilaian && $penilaian->denominator_value > 0) {
                            return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                        }
                        return 0;
                    });
                    return $data->profiles->count() > 0 ? $totalScore / $data->profiles->count() : 0;
                });

                $bestPerformer = $group->sortByDesc(function ($data) {
                    $totalScore = $data->profiles->sum(function ($profile) {
                        $penilaian = $profile->penilaian->first();
                        if ($penilaian && $penilaian->denominator_value > 0) {
                            return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                        }
                        return 0;
                    });
                    return $data->profiles->count() > 0 ? $totalScore / $data->profiles->count() : 0;
                })->first();

                return [
                    'type' => $type,
                    'count' => $group->count(),
                    'average_score' => round($averageScore, 2),
                    'best_performer' => optional($bestPerformer->unitKerja->first())->name ?? 'Unknown'
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Identify best practices
     */
    private function identifyBestPractices(Collection $imutData): array
    {
        $topPerformers = $imutData
            ->filter(function ($data) {
                $averageScore = $data->profiles->avg(function ($profile) {
                    $penilaian = $profile->penilaian->first();
                    if ($penilaian && $penilaian->denominator_value > 0) {
                        return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                    }
                    return 0;
                });
                return $averageScore >= 85;
            })
            ->take(5);

        return $topPerformers->map(function ($data) {
            $averageScore = $data->profiles->avg(function ($profile) {
                $penilaian = $profile->penilaian->first();
                if ($penilaian && $penilaian->denominator_value > 0) {
                    return ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                }
                return 0;
            });

            return [
                'unit_kerja_name' => optional($data->unitKerja->first())->name ?? 'Unknown',
                'average_score' => round($averageScore, 2),
                'strength_areas' => $data->profiles
                    ->filter(function ($profile) {
                        $penilaian = $profile->penilaian->first();
                        if ($penilaian && $penilaian->denominator_value > 0) {
                            $score = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                            return $score >= 90;
                        }
                        return false;
                    })
                    ->map(function ($profile) {
                        return optional($profile->imutData->categories)->name ?? 'Unknown';
                    })
                    ->unique()
                    ->values()
                    ->toArray()
            ];
        })->toArray();
    }

    /**
     * Identify improvement opportunities
     */
    private function identifyImprovementOpportunities(Collection $imutData): array
    {
        $opportunities = [];

        foreach ($imutData as $data) {
            $weakProfiles = $data->profiles->filter(function ($profile) {
                $penilaian = $profile->penilaian->first();
                if ($penilaian && $penilaian->denominator_value > 0) {
                    $score = ($penilaian->numerator_value / $penilaian->denominator_value) * 100;
                    return $score < 70;
                }
                return false;
            });

            if ($weakProfiles->isNotEmpty()) {
                $weakAreas = $weakProfiles
                    ->groupBy(function ($profile) {
                        return optional($profile->imutData->categories)->name ?? 'Unknown';
                    })
                    ->map->count()
                    ->sortDesc();

                $opportunities[] = [
                    'unit_kerja_name' => optional($data->unitKerja->first())->name ?? 'Unknown',
                    'weak_areas' => $weakAreas->take(3)->toArray(),
                    'priority_score' => $weakAreas->sum()
                ];
            }
        }

        return collect($opportunities)
            ->sortByDesc('priority_score')
            ->take(10)
            ->values()
            ->toArray();
    }
}
