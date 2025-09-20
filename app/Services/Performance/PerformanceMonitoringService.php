<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Performance Monitoring Service
 *
 * Monitors application performance metrics and provides insights
 */
class PerformanceMonitoringService
{
    protected array $thresholds;

    public function __construct()
    {
        $this->thresholds = config('performance.thresholds', []);
    }

    /**
     * Record performance metric
     */
    public function recordMetric(string $type, float $value, array $context = []): void
    {
        $metric = [
            'type' => $type,
            'value' => $value,
            'context' => $context,
            'timestamp' => now(),
            'memory_usage' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
        ];

        // Log to performance channel
        Log::channel('performance')->info("Performance metric: {$type}", $metric);

        // Store in cache for real-time monitoring
        $this->storeMetricInCache($type, $value, $context);

        // Check for performance alerts
        $this->checkPerformanceAlert($type, $value, $context);
    }

    /**
     * Store metric in cache for aggregation
     */
    protected function storeMetricInCache(string $type, float $value, array $context): void
    {
        $hourKey = "performance:{$type}:" . now()->format('Y-m-d-H');
        $minuteKey = "performance:{$type}:" . now()->format('Y-m-d-H-i');

        // Store individual metric
        Cache::rpush($minuteKey, json_encode([
            'value' => $value,
            'context' => $context,
            'timestamp' => now()->timestamp,
        ]));
        Cache::expire($minuteKey, 3600); // 1 hour

        // Update aggregated statistics
        $this->updateAggregatedStats($type, $value, $hourKey);
    }

    /**
     * Update aggregated statistics
     */
    protected function updateAggregatedStats(string $type, float $value, string $hourKey): void
    {
        $statsKey = "{$hourKey}:stats";
        $currentStats = Cache::get($statsKey, [
            'count' => 0,
            'sum' => 0,
            'min' => null,
            'max' => null,
            'avg' => 0,
        ]);

        $currentStats['count']++;
        $currentStats['sum'] += $value;
        $currentStats['min'] = $currentStats['min'] === null ? $value : min($currentStats['min'], $value);
        $currentStats['max'] = $currentStats['max'] === null ? $value : max($currentStats['max'], $value);
        $currentStats['avg'] = $currentStats['sum'] / $currentStats['count'];

        Cache::put($statsKey, $currentStats, 3600);
    }

    /**
     * Check for performance alerts
     */
    protected function checkPerformanceAlert(string $type, float $value, array $context): void
    {
        $threshold = $this->thresholds[$type] ?? null;

        if (!$threshold || $value < $threshold['critical']) {
            return;
        }

        $alertKey = "performance_alert:{$type}:" . now()->format('Y-m-d-H');

        // Only send one alert per hour per type
        if (Cache::has($alertKey)) {
            return;
        }

        $alert = [
            'type' => $type,
            'value' => $value,
            'threshold' => $threshold,
            'context' => $context,
            'timestamp' => now(),
        ];

        Log::channel('performance')->warning('Performance alert triggered', $alert);
        Cache::put($alertKey, true, 3600);
    }

    /**
     * Monitor request performance
     */
    public function monitorRequest(Request $request, float $startTime, float $startMemory): void
    {
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = memory_get_peak_usage(true) - $startMemory;

        $context = [
            'url' => $request->url(),
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ];

        $this->recordMetric('request_duration', $duration, $context);
        $this->recordMetric('memory_usage', $memoryUsage, $context);
    }

    /**
     * Monitor database query performance
     */
    public function monitorQuery(string $sql, array $bindings, float $duration): void
    {
        $context = [
            'sql' => $sql,
            'bindings' => $bindings,
            'connection' => DB::getDefaultConnection(),
        ];

        $this->recordMetric('query_duration', $duration, $context);

        // Check for slow queries
        $slowQueryThreshold = $this->thresholds['slow_query']['warning'] ?? 1000;
        if ($duration > $slowQueryThreshold) {
            Log::channel('performance')->warning('Slow query detected', [
                'sql' => $sql,
                'duration' => $duration,
                'bindings' => $bindings,
            ]);
        }
    }

    /**
     * Monitor cache performance
     */
    public function monitorCache(string $operation, string $key, bool $hit, float $duration): void
    {
        $context = [
            'operation' => $operation,
            'key' => $key,
            'hit' => $hit,
        ];

        $this->recordMetric('cache_duration', $duration, $context);
        $this->recordMetric('cache_hit_rate', $hit ? 1 : 0, $context);
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats(string $period = 'hour'): array
    {
        $format = $period === 'hour' ? 'Y-m-d-H' : 'Y-m-d';
        $key = now()->format($format);

        $metricTypes = [
            'request_duration',
            'memory_usage',
            'query_duration',
            'cache_duration',
        ];

        $stats = [];
        foreach ($metricTypes as $type) {
            $statsKey = "performance:{$type}:{$key}:stats";
            $stats[$type] = Cache::get($statsKey, [
                'count' => 0,
                'avg' => 0,
                'min' => null,
                'max' => null,
            ]);
        }

        return $stats;
    }

    /**
     * Get performance trends
     */
    public function getPerformanceTrends(int $hours = 24): array
    {
        $trends = [];
        $now = now();

        for ($i = 0; $i < $hours; $i++) {
            $hour = $now->copy()->subHours($i);
            $key = $hour->format('Y-m-d-H');

            $hourStats = [];
            $metricTypes = ['request_duration', 'memory_usage', 'query_duration'];

            foreach ($metricTypes as $type) {
                $statsKey = "performance:{$type}:{$key}:stats";
                $stats = Cache::get($statsKey, ['avg' => 0, 'count' => 0]);
                $hourStats[$type] = [
                    'avg' => round($stats['avg'], 2),
                    'count' => $stats['count'],
                ];
            }

            $trends[$key] = $hourStats;
        }

        return array_reverse($trends, true);
    }

    /**
     * Get slow queries
     */
    public function getSlowQueries(int $limit = 20): array
    {
        $slowQueries = [];
        $pattern = "performance:query_duration:*";

        // This is simplified - in production you might use a different approach
        $hours = 24;
        $threshold = $this->thresholds['slow_query']['warning'] ?? 1000;

        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $minuteKeys = [];

            for ($m = 0; $m < 60; $m++) {
                $minute = now()->subHours($i)->minute($m)->format('Y-m-d-H-i');
                $minuteKey = "performance:query_duration:{$minute}";

                if (Cache::has($minuteKey)) {
                    $metrics = Cache::lrange($minuteKey, 0, -1);
                    foreach ($metrics as $metric) {
                        $data = json_decode($metric, true);
                        if ($data['value'] > $threshold) {
                            $slowQueries[] = [
                                'duration' => $data['value'],
                                'sql' => $data['context']['sql'] ?? 'Unknown',
                                'timestamp' => $data['timestamp'],
                            ];
                        }
                    }
                }
            }
        }

        // Sort by duration (slowest first) and limit
        usort($slowQueries, fn($a, $b) => $b['duration'] <=> $a['duration']);

        return array_slice($slowQueries, 0, $limit);
    }

    /**
     * Get top resource consumers
     */
    public function getTopResourceConsumers(string $metric = 'request_duration', int $limit = 10): array
    {
        $consumers = [];
        $hours = 24;

        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');

            for ($m = 0; $m < 60; $m++) {
                $minute = now()->subHours($i)->minute($m)->format('Y-m-d-H-i');
                $minuteKey = "performance:{$metric}:{$minute}";

                if (Cache::has($minuteKey)) {
                    $metrics = Cache::lrange($minuteKey, 0, -1);
                    foreach ($metrics as $metricData) {
                        $data = json_decode($metricData, true);
                        $route = $data['context']['route'] ?? $data['context']['url'] ?? 'Unknown';

                        if (!isset($consumers[$route])) {
                            $consumers[$route] = [
                                'route' => $route,
                                'total_value' => 0,
                                'count' => 0,
                                'avg_value' => 0,
                            ];
                        }

                        $consumers[$route]['total_value'] += $data['value'];
                        $consumers[$route]['count']++;
                        $consumers[$route]['avg_value'] = $consumers[$route]['total_value'] / $consumers[$route]['count'];
                    }
                }
            }
        }

        // Sort by average value and limit
        uasort($consumers, fn($a, $b) => $b['avg_value'] <=> $a['avg_value']);

        return array_slice(array_values($consumers), 0, $limit);
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport(string $period = 'day'): array
    {
        return [
            'period' => $period,
            'generated_at' => now(),
            'stats' => $this->getPerformanceStats($period),
            'trends' => $this->getPerformanceTrends($period === 'day' ? 24 : 168),
            'slow_queries' => $this->getSlowQueries(),
            'top_consumers' => [
                'by_duration' => $this->getTopResourceConsumers('request_duration'),
                'by_memory' => $this->getTopResourceConsumers('memory_usage'),
            ],
            'system_info' => $this->getSystemInfo(),
        ];
    }

    /**
     * Get system information
     */
    protected function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'current_memory_usage' => memory_get_usage(true),
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
        ];
    }

    /**
     * Clean old performance data
     */
    public function cleanOldData(int $daysToKeep = 7): int
    {
        $cleaned = 0;
        $cutoffDate = now()->subDays($daysToKeep);

        // This is a simplified cleanup - in production you might use Redis SCAN
        $metricTypes = ['request_duration', 'memory_usage', 'query_duration', 'cache_duration'];

        foreach ($metricTypes as $type) {
            for ($i = $daysToKeep; $i < $daysToKeep + 30; $i++) {
                $date = now()->subDays($i);
                $dayKey = "performance:{$type}:" . $date->format('Y-m-d');

                if (Cache::has($dayKey)) {
                    Cache::forget($dayKey);
                    $cleaned++;
                }

                // Clean hourly data
                for ($h = 0; $h < 24; $h++) {
                    $hourKey = "performance:{$type}:" . $date->format('Y-m-d') . "-{$h}";
                    if (Cache::has($hourKey)) {
                        Cache::forget($hourKey);
                        $cleaned++;
                    }
                }
            }
        }

        return $cleaned;
    }
}
