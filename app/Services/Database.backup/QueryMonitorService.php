<?php

namespace App\Services\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Database Query Monitor Service
 * 
 * Monitors and logs slow queries, provides optimization suggestions
 */
class QueryMonitorService
{
    protected float $slowQueryThreshold;
    protected bool $logSlowQueries;
    protected bool $cacheQueryStats;

    public function __construct()
    {
        $this->slowQueryThreshold = config('database.monitoring.slow_query_threshold', 1000); // ms
        $this->logSlowQueries = config('database.monitoring.log_slow_queries', true);
        $this->cacheQueryStats = config('database.monitoring.cache_stats', true);
    }

    /**
     * Enable query monitoring
     */
    public function enableMonitoring(): void
    {
        DB::listen(function (QueryExecuted $query) {
            $this->logQuery($query);
            
            if ($query->time >= $this->slowQueryThreshold) {
                $this->handleSlowQuery($query);
            }
            
            if ($this->cacheQueryStats) {
                $this->updateQueryStats($query);
            }
        });
    }

    /**
     * Log query execution
     */
    protected function logQuery(QueryExecuted $query): void
    {
        if (app()->environment('production') && !$this->logSlowQueries) {
            return;
        }

        $context = [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'connection' => $query->connectionName,
        ];

        if ($query->time >= $this->slowQueryThreshold) {
            Log::warning('Slow query detected', $context);
        } else {
            Log::debug('Query executed', $context);
        }
    }

    /**
     * Handle slow query detection
     */
    protected function handleSlowQuery(QueryExecuted $query): void
    {
        $suggestion = $this->generateOptimizationSuggestion($query);
        
        $context = [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'suggestion' => $suggestion,
            'connection' => $query->connectionName,
        ];

        Log::warning('Slow query optimization needed', $context);

        // Store in cache for dashboard monitoring
        $cacheKey = 'slow_queries:' . date('Y-m-d');
        $slowQueries = Cache::get($cacheKey, []);
        $slowQueries[] = [
            'timestamp' => now(),
            'sql' => $query->sql,
            'time' => $query->time,
            'suggestion' => $suggestion,
        ];
        
        Cache::put($cacheKey, $slowQueries, now()->addDays(7));
    }

    /**
     * Generate optimization suggestions based on query pattern
     */
    protected function generateOptimizationSuggestion(QueryExecuted $query): array
    {
        $sql = strtolower($query->sql);
        $suggestions = [];

        // Check for missing indexes
        if (str_contains($sql, 'where') && !str_contains($sql, 'index')) {
            $suggestions[] = 'Consider adding indexes on WHERE clause columns';
        }

        // Check for N+1 queries
        if (str_contains($sql, 'select') && str_contains($sql, 'where') && 
            str_contains($sql, 'in (')) {
            $suggestions[] = 'Potential N+1 query - consider using eager loading';
        }

        // Check for full table scans
        if (str_contains($sql, 'select') && !str_contains($sql, 'where') && 
            !str_contains($sql, 'limit')) {
            $suggestions[] = 'Full table scan detected - add WHERE clause or LIMIT';
        }

        // Check for inefficient JOINs
        if (str_contains($sql, 'join') && $query->time > 2000) {
            $suggestions[] = 'Slow JOIN detected - verify indexes on JOIN columns';
        }

        // Check for sorting without index
        if (str_contains($sql, 'order by') && $query->time > 1000) {
            $suggestions[] = 'Slow ORDER BY - consider adding index on sort columns';
        }

        return $suggestions;
    }

    /**
     * Update query statistics
     */
    protected function updateQueryStats(QueryExecuted $query): void
    {
        $statsKey = 'query_stats:' . date('Y-m-d');
        $stats = Cache::get($statsKey, [
            'total_queries' => 0,
            'total_time' => 0,
            'slow_queries' => 0,
            'avg_time' => 0,
            'by_table' => [],
        ]);

        $stats['total_queries']++;
        $stats['total_time'] += $query->time;
        
        if ($query->time >= $this->slowQueryThreshold) {
            $stats['slow_queries']++;
        }
        
        $stats['avg_time'] = $stats['total_time'] / $stats['total_queries'];

        // Extract table name for table-specific stats
        $tableName = $this->extractTableName($query->sql);
        if ($tableName) {
            if (!isset($stats['by_table'][$tableName])) {
                $stats['by_table'][$tableName] = [
                    'queries' => 0,
                    'total_time' => 0,
                    'avg_time' => 0,
                ];
            }
            
            $stats['by_table'][$tableName]['queries']++;
            $stats['by_table'][$tableName]['total_time'] += $query->time;
            $stats['by_table'][$tableName]['avg_time'] = 
                $stats['by_table'][$tableName]['total_time'] / 
                $stats['by_table'][$tableName]['queries'];
        }

        Cache::put($statsKey, $stats, now()->addDays(7));
    }

    /**
     * Extract table name from SQL query
     */
    protected function extractTableName(string $sql): ?string
    {
        $sql = strtolower(trim($sql));
        
        // Match different SQL patterns
        $patterns = [
            '/from\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i',
            '/update\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i',
            '/insert\s+into\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i',
            '/delete\s+from\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sql, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Get query statistics for dashboard
     */
    public function getQueryStats(string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        $statsKey = 'query_stats:' . $date;
        
        return Cache::get($statsKey, [
            'total_queries' => 0,
            'total_time' => 0,
            'slow_queries' => 0,
            'avg_time' => 0,
            'by_table' => [],
        ]);
    }

    /**
     * Get slow queries for analysis
     */
    public function getSlowQueries(string $date = null): array
    {
        $date = $date ?: date('Y-m-d');
        $cacheKey = 'slow_queries:' . $date;
        
        return Cache::get($cacheKey, []);
    }

    /**
     * Analyze query performance trends
     */
    public function getPerformanceTrends(int $days = 7): array
    {
        $trends = [];
        $endDate = now();
        
        for ($i = 0; $i < $days; $i++) {
            $date = $endDate->copy()->subDays($i)->format('Y-m-d');
            $stats = $this->getQueryStats($date);
            
            $trends[$date] = [
                'total_queries' => $stats['total_queries'],
                'slow_queries' => $stats['slow_queries'],
                'avg_time' => round($stats['avg_time'], 2),
                'slow_query_percentage' => $stats['total_queries'] > 0 
                    ? round(($stats['slow_queries'] / $stats['total_queries']) * 100, 2)
                    : 0,
            ];
        }

        return array_reverse($trends, true);
    }

    /**
     * Get top slowest tables
     */
    public function getSlowTables(int $limit = 10): array
    {
        $stats = $this->getQueryStats();
        $tables = $stats['by_table'] ?? [];
        
        // Sort by average time
        uasort($tables, function ($a, $b) {
            return $b['avg_time'] <=> $a['avg_time'];
        });

        return array_slice($tables, 0, $limit, true);
    }

    /**
     * Clear monitoring cache
     */
    public function clearCache(): void
    {
        $pattern = 'query_stats:*';
        Cache::flush(); // Consider more specific cache clearing in production
    }
}