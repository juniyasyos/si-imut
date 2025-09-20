<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Cache Manager
 *
 * Centralized cache management service that coordinates all cache services,
 * provides cache health monitoring, handles cache warming, and implements
 * sophisticated invalidation strategies.
 */
class CacheManager
{
    private LaporanImutCacheService $laporanImutCache;
    private ImutDataCacheService $imutDataCache;
    private UserCacheService $userCache;

    public function __construct(
        LaporanImutCacheService $laporanImutCache,
        ImutDataCacheService $imutDataCache,
        UserCacheService $userCache
    ) {
        $this->laporanImutCache = $laporanImutCache;
        $this->imutDataCache = $imutDataCache;
        $this->userCache = $userCache;
    }

    /**
     * Warm up critical caches
     */
    public function warmUp(): array
    {
        $results = [];
        $startTime = microtime(true);

        try {
            Log::info('Starting cache warm-up process');

            // Warm up dashboard summary (most frequently accessed)
            $results['dashboard_summary'] = $this->warmUpDashboardSummary();

            // Warm up global metrics
            $results['global_metrics'] = $this->warmUpGlobalMetrics();

            // Warm up role statistics
            $results['role_statistics'] = $this->warmUpRoleStatistics();

            // Warm up assessment periods
            $results['assessment_periods'] = $this->warmUpAssessmentPeriods();

            $endTime = microtime(true);
            $totalTime = round($endTime - $startTime, 2);

            Log::info('Cache warm-up completed', [
                'duration_seconds' => $totalTime,
                'items_warmed' => count(array_filter($results))
            ]);

            return [
                'success' => true,
                'duration_seconds' => $totalTime,
                'results' => $results,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Cache warm-up failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Get cache health status
     */
    public function getHealthStatus(): array
    {
        $status = [
            'overall_status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'stores' => [],
            'services' => [],
            'metrics' => []
        ];

        try {
            $cacheStore = config('cache.default');

            // Check for invalid cache store
            $validStores = ['redis', 'database', 'array', 'file'];
            if (!in_array($cacheStore, $validStores)) {
                $status['overall_status'] = 'unhealthy';
                $status['stores']['invalid'] = [
                    'status' => 'unhealthy',
                    'error' => "Invalid cache store: {$cacheStore}"
                ];
                return $status;
            }

            // Check Redis connection (only if Redis is configured)
            if ($cacheStore === 'redis') {
                $status['stores']['redis'] = $this->checkRedisHealth();
            }

            // Check database cache table (if using database cache)
            if ($cacheStore === 'database') {
                $status['stores']['database'] = $this->checkDatabaseCacheHealth();
            }

            // Check array cache (if using array cache)
            if ($cacheStore === 'array') {
                $status['stores']['array'] = $this->checkArrayCacheHealth();
            }

            // Check each cache service
            $status['services']['laporan_imut'] = $this->checkServiceHealth($this->laporanImutCache);
            $status['services']['imut_data'] = $this->checkServiceHealth($this->imutDataCache);
            $status['services']['user'] = $this->checkServiceHealth($this->userCache);

            // Get cache metrics
            $status['metrics'] = $this->getCacheMetrics();

            // Determine overall status
            $hasFailures = collect($status['stores'])->contains('status', 'unhealthy') ||
                          collect($status['services'])->contains('status', 'unhealthy');

            $status['overall_status'] = $hasFailures ? 'degraded' : 'healthy';

        } catch (\Exception $e) {
            $status['overall_status'] = 'unhealthy';
            $status['error'] = $e->getMessage();

            Log::error('Cache health check failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $status;
    }

    /**
     * Flush all caches
     */
    public function flushAll(): array
    {
        $results = [];

        try {
            Log::info('Starting cache flush operation');

            // Flush individual service caches
            $results['laporan_imut'] = $this->laporanImutCache->flush();
            $results['imut_data'] = $this->imutDataCache->flush();
            $results['user'] = $this->userCache->flush();

            // Flush Redis entirely (if using Redis)
            if (config('cache.default') === 'redis') {
                Redis::flushdb();
                $results['redis_flush'] = true;
            }

            Log::info('Cache flush completed', $results);

            return [
                'success' => true,
                'results' => $results,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Cache flush failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Handle model events for cache invalidation
     */
    public function handleModelEvent(string $model, string $event, $modelInstance): void
    {
        try {
            switch ($model) {
                case 'LaporanImut':
                    $this->handleLaporanImutEvent($event, $modelInstance);
                    break;

                case 'ImutData':
                    $this->handleImutDataEvent($event, $modelInstance);
                    break;

                case 'ImutProfile':
                    $this->handleImutProfileEvent($event, $modelInstance);
                    break;

                case 'User':
                    $this->handleUserEvent($event, $modelInstance);
                    break;

                case 'UnitKerja':
                    $this->handleUnitKerjaEvent($event, $modelInstance);
                    break;
            }

        } catch (\Exception $e) {
            Log::warning('Cache invalidation failed for model event', [
                'model' => $model,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getStatistics(): array
    {
        try {
            $cacheStore = config('cache.default');

            // Check for invalid cache store
            $validStores = ['redis', 'database', 'array', 'file'];
            if (!in_array($cacheStore, $validStores)) {
                return [
                    'error' => "Invalid cache store: {$cacheStore}",
                    'timestamp' => now()->toISOString(),
                    'default_store' => $cacheStore
                ];
            }

            $stats = [
                'timestamp' => now()->toISOString(),
                'default_store' => $cacheStore,
                'stores' => [],
                'memory_usage' => [],
                'hit_rates' => []
            ];

            // Redis statistics
            if ($cacheStore === 'redis' && class_exists('Redis')) {
                try {
                    $redisInfo = Redis::info();
                    $stats['stores']['redis'] = [
                        'used_memory' => $redisInfo['used_memory'] ?? 'N/A',
                        'used_memory_human' => $redisInfo['used_memory_human'] ?? 'N/A',
                        'connected_clients' => $redisInfo['connected_clients'] ?? 'N/A',
                        'total_commands_processed' => $redisInfo['total_commands_processed'] ?? 'N/A',
                        'keyspace_hits' => $redisInfo['keyspace_hits'] ?? 0,
                        'keyspace_misses' => $redisInfo['keyspace_misses'] ?? 0
                    ];

                    // Calculate hit rate
                    $hits = (int)($redisInfo['keyspace_hits'] ?? 0);
                    $misses = (int)($redisInfo['keyspace_misses'] ?? 0);
                    $total = $hits + $misses;

                    $stats['hit_rates']['redis'] = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
                } catch (\Exception $e) {
                    $stats['stores']['redis'] = [
                        'error' => 'Redis statistics unavailable: ' . $e->getMessage()
                    ];
                    $stats['hit_rates']['redis'] = 0;
                }
            } else {
                $stats['stores']['redis'] = [
                    'message' => 'Redis not configured or not available'
                ];
                $stats['hit_rates']['redis'] = 0;
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get cache statistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Optimize cache performance
     */
    public function optimize(): array
    {
        $results = [];

        try {
            Log::info('Starting cache optimization');

            // Clean up expired keys (Redis)
            if (config('cache.default') === 'redis') {
                $results['redis_cleanup'] = $this->cleanupRedisKeys();
            }

            // Warm up critical paths
            $results['warm_up'] = $this->warmUp();

            // Compact memory (if possible)
            $results['memory_optimization'] = $this->optimizeMemory();

            Log::info('Cache optimization completed', $results);

            return [
                'success' => true,
                'results' => $results,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Cache optimization failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    // Private helper methods

    private function warmUpDashboardSummary(): bool
    {
        try {
            $this->laporanImutCache->getDashboardSummary();
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to warm up dashboard summary', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function warmUpGlobalMetrics(): bool
    {
        try {
            $this->imutDataCache->getGlobalMetrics();
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to warm up global metrics', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function warmUpRoleStatistics(): bool
    {
        try {
            $this->userCache->getRoleStatistics();
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to warm up role statistics', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function warmUpAssessmentPeriods(): bool
    {
        try {
            $this->laporanImutCache->getAssessmentPeriods();
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to warm up assessment periods', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function checkArrayCacheHealth(): array
    {
        try {
            // Array cache is always healthy (in-memory)
            return [
                'status' => 'healthy',
                'response_time' => 0,
                'message' => 'Array cache is operational'
            ];
        } catch (\Exception $e) {
            Log::warning('Array cache health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'response_time' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkRedisHealth(): array
    {
        try {
            if (!class_exists('Redis')) {
                return [
                    'status' => 'unavailable',
                    'error' => 'Redis PHP extension not installed',
                    'connection' => 'not_available'
                ];
            }

            Redis::ping();
            return [
                'status' => 'healthy',
                'response_time' => $this->measureResponseTime(fn() => Redis::ping()),
                'connection' => 'active'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'connection' => 'failed'
            ];
        }
    }

    private function checkDatabaseCacheHealth(): array
    {
        try {
            $count = DB::table('cache')->count();
            return [
                'status' => 'healthy',
                'table_exists' => true,
                'record_count' => $count
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'table_exists' => false
            ];
        }
    }

    private function checkServiceHealth(BaseCacheService $service): array
    {
        try {
            // Test basic cache operations
            $testKey = 'health_check_' . uniqid();
            $testValue = 'test_' . time();

            $service->put($testKey, $testValue, 60);
            $retrieved = $service->get($testKey);
            $service->forget($testKey);

            return [
                'status' => $retrieved === $testValue ? 'healthy' : 'degraded',
                'operations' => [
                    'put' => true,
                    'get' => $retrieved === $testValue,
                    'forget' => true
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    private function getCacheMetrics(): array
    {
        try {
            return [
                'total_keys' => $this->countTotalKeys(),
                'memory_usage' => $this->getMemoryUsage(),
                'hit_rate' => $this->calculateHitRate()
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    private function countTotalKeys(): int
    {
        try {
            if (config('cache.default') === 'redis' && class_exists('Redis')) {
                return Redis::dbsize();
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getMemoryUsage(): string
    {
        try {
            if (config('cache.default') === 'redis' && class_exists('Redis')) {
                $info = Redis::info();
                return $info['used_memory_human'] ?? 'N/A';
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function calculateHitRate(): float
    {
        try {
            if (config('cache.default') === 'redis' && class_exists('Redis')) {
                $info = Redis::info();
                $hits = (int)($info['keyspace_hits'] ?? 0);
                $misses = (int)($info['keyspace_misses'] ?? 0);
                $total = $hits + $misses;

                return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function measureResponseTime(callable $operation): float
    {
        $start = microtime(true);
        $operation();
        return round((microtime(true) - $start) * 1000, 2); // milliseconds
    }

    private function cleanupRedisKeys(): array
    {
        try {
            // This would implement Redis key cleanup logic
            // For now, return a mock result
            return [
                'keys_scanned' => 0,
                'keys_deleted' => 0,
                'memory_freed' => '0MB'
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    private function optimizeMemory(): array
    {
        try {
            // Memory optimization logic would go here
            return [
                'memory_before' => $this->getMemoryUsage(),
                'memory_after' => $this->getMemoryUsage(),
                'optimization_applied' => false
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    // Event handlers for cache invalidation

    private function handleLaporanImutEvent(string $event, $laporan): void
    {
        if (in_array($event, ['created', 'updated', 'deleted'])) {
            $this->laporanImutCache->invalidateLaporan($laporan->id);
            $this->laporanImutCache->invalidateListCaches();
        }
    }

    private function handleImutDataEvent(string $event, $imutData): void
    {
        if (in_array($event, ['created', 'updated', 'deleted'])) {
            $this->imutDataCache->invalidateImutData($imutData->id);
            $this->imutDataCache->invalidateGlobalCaches();
        }
    }

    private function handleImutProfileEvent(string $event, $profile): void
    {
        if (in_array($event, ['created', 'updated', 'deleted'])) {
            // Get the category through the ImutData relationship
            if ($profile->imutData && $profile->imutData->imut_kategori_id) {
                $this->imutDataCache->invalidateCategoryCache($profile->imutData->imut_kategori_id);
            }
            $this->imutDataCache->invalidateImutData($profile->imut_data_id);
        }
    }

    private function handleUserEvent(string $event, $user): void
    {
        if (in_array($event, ['created', 'updated', 'deleted'])) {
            $this->userCache->invalidateUser($user->id);
        }
    }

    private function handleUnitKerjaEvent(string $event, $unitKerja): void
    {
        if (in_array($event, ['created', 'updated', 'deleted'])) {
            $this->imutDataCache->invalidateUnitKerjaCache($unitKerja->id);
            $this->laporanImutCache->invalidateUnitKerjaCache($unitKerja->id);
        }
    }
}
