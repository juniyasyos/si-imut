<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Database Connection Optimizer Service
 * 
 * Optimizes database connections, manages connection pooling,
 * and provides health monitoring
 */
class ConnectionOptimizerService
{
    protected array $connectionStats = [];

    /**
     * Initialize connection optimization
     */
    public function initialize(): void
    {
        $this->configureConnections();
        $this->enableConnectionMonitoring();
    }

    /**
     * Configure database connections for optimal performance
     */
    protected function configureConnections(): void
    {
        $connections = config('database.connections');
        
        foreach ($connections as $name => $config) {
            if (in_array($config['driver'], ['mysql', 'pgsql'])) {
                $this->optimizeConnection($name, $config);
            }
        }
    }

    /**
     * Optimize specific database connection
     */
    protected function optimizeConnection(string $connectionName, array $config): void
    {
        $connection = DB::connection($connectionName);
        
        switch ($config['driver']) {
            case 'mysql':
                $this->optimizeMySQLConnection($connection);
                break;
            case 'pgsql':
                $this->optimizePostgreSQLConnection($connection);
                break;
        }
    }

    /**
     * Optimize MySQL connection settings
     */
    protected function optimizeMySQLConnection($connection): void
    {
        try {
            // Set session variables for better performance
            $optimizations = [
                // Query cache (if enabled on server)
                "SET SESSION query_cache_type = ON",
                
                // Increase sort buffer for ORDER BY operations
                "SET SESSION sort_buffer_size = 2097152", // 2MB
                
                // Optimize JOIN buffer
                "SET SESSION join_buffer_size = 1048576", // 1MB
                
                // Increase read buffer for table scans
                "SET SESSION read_buffer_size = 1048576", // 1MB
                
                // Optimize bulk insert buffer
                "SET SESSION bulk_insert_buffer_size = 8388608", // 8MB
                
                // Set connection timeout
                "SET SESSION wait_timeout = 28800", // 8 hours
                "SET SESSION interactive_timeout = 28800",
                
                // Optimize for InnoDB
                "SET SESSION tx_isolation = 'READ-COMMITTED'",
            ];

            foreach ($optimizations as $sql) {
                $connection->statement($sql);
            }
            
            Log::info('MySQL connection optimized', ['connection' => $connection->getName()]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to optimize MySQL connection', [
                'connection' => $connection->getName(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Optimize PostgreSQL connection settings
     */
    protected function optimizePostgreSQLConnection($connection): void
    {
        try {
            // PostgreSQL session optimizations
            $optimizations = [
                // Increase work memory for complex queries
                "SET work_mem = '16MB'",
                
                // Optimize maintenance work memory
                "SET maintenance_work_mem = '64MB'",
                
                // Set random page cost (for SSD storage)
                "SET random_page_cost = 1.1",
                
                // Optimize effective cache size
                "SET effective_cache_size = '256MB'",
                
                // Set application name for monitoring
                "SET application_name = 'si-imut-app'",
                
                // Set timezone
                "SET timezone = 'Asia/Jakarta'",
            ];

            foreach ($optimizations as $sql) {
                $connection->statement($sql);
            }
            
            Log::info('PostgreSQL connection optimized', ['connection' => $connection->getName()]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to optimize PostgreSQL connection', [
                'connection' => $connection->getName(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enable connection monitoring
     */
    protected function enableConnectionMonitoring(): void
    {
        // Monitor connection events
        DB::listen(function ($query) {
            $this->recordConnectionUsage($query->connectionName);
        });
    }

    /**
     * Record connection usage statistics
     */
    protected function recordConnectionUsage(string $connectionName): void
    {
        $key = 'db_connections:' . date('Y-m-d-H');
        $stats = Cache::get($key, []);
        
        if (!isset($stats[$connectionName])) {
            $stats[$connectionName] = [
                'queries' => 0,
                'last_used' => null,
            ];
        }
        
        $stats[$connectionName]['queries']++;
        $stats[$connectionName]['last_used'] = now()->toISOString();
        
        Cache::put($key, $stats, now()->addDays(1));
    }

    /**
     * Get connection health status
     */
    public function getConnectionHealth(): array
    {
        $health = [];
        $connections = config('database.connections');
        
        foreach (array_keys($connections) as $connectionName) {
            try {
                $connection = DB::connection($connectionName);
                $startTime = microtime(true);
                
                // Test connection with simple query
                $connection->select('SELECT 1 as test');
                
                $responseTime = (microtime(true) - $startTime) * 1000; // Convert to ms
                
                $health[$connectionName] = [
                    'status' => 'healthy',
                    'response_time_ms' => round($responseTime, 2),
                    'driver' => $connections[$connectionName]['driver'],
                    'host' => $connections[$connectionName]['host'] ?? 'local',
                    'database' => $connections[$connectionName]['database'] ?? 'unknown',
                ];
                
            } catch (\Exception $e) {
                $health[$connectionName] = [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage(),
                    'driver' => $connections[$connectionName]['driver'],
                    'host' => $connections[$connectionName]['host'] ?? 'local',
                    'database' => $connections[$connectionName]['database'] ?? 'unknown',
                ];
            }
        }
        
        return $health;
    }

    /**
     * Get connection usage statistics
     */
    public function getConnectionStats(int $hours = 24): array
    {
        $stats = [];
        $now = now();
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = $now->copy()->subHours($i);
            $key = 'db_connections:' . $hour->format('Y-m-d-H');
            $hourStats = Cache::get($key, []);
            
            if (!empty($hourStats)) {
                $stats[$hour->format('Y-m-d H:00')] = $hourStats;
            }
        }
        
        return array_reverse($stats, true);
    }

    /**
     * Optimize connection pool
     */
    public function optimizeConnectionPool(): array
    {
        $recommendations = [];
        $stats = $this->getConnectionStats(24);
        
        foreach ($stats as $hour => $connections) {
            foreach ($connections as $connectionName => $connectionStats) {
                if (!isset($recommendations[$connectionName])) {
                    $recommendations[$connectionName] = [
                        'total_queries' => 0,
                        'peak_hour_queries' => 0,
                        'recommendations' => [],
                    ];
                }
                
                $recommendations[$connectionName]['total_queries'] += $connectionStats['queries'];
                
                if ($connectionStats['queries'] > $recommendations[$connectionName]['peak_hour_queries']) {
                    $recommendations[$connectionName]['peak_hour_queries'] = $connectionStats['queries'];
                }
            }
        }
        
        // Generate recommendations based on usage patterns
        foreach ($recommendations as $connectionName => &$data) {
            $avgQueriesPerHour = $data['total_queries'] / 24;
            $peakQueries = $data['peak_hour_queries'];
            
            if ($peakQueries > 1000) {
                $data['recommendations'][] = 'Consider increasing connection pool size for high load';
            }
            
            if ($avgQueriesPerHour < 10) {
                $data['recommendations'][] = 'Low usage detected - consider reducing connection pool size';
            }
            
            if ($peakQueries > $avgQueriesPerHour * 5) {
                $data['recommendations'][] = 'High peak load detected - consider implementing read replicas';
            }
        }
        
        return $recommendations;
    }

    /**
     * Test database performance
     */
    public function runPerformanceTest(string $connectionName = null): array
    {
        $connectionName = $connectionName ?: config('database.default');
        $connection = DB::connection($connectionName);
        
        $tests = [];
        
        // Test 1: Simple SELECT query
        $startTime = microtime(true);
        $connection->select('SELECT 1 as test');
        $tests['simple_select'] = [
            'time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'status' => 'passed'
        ];
        
        // Test 2: COUNT query on a real table
        try {
            $startTime = microtime(true);
            $connection->select('SELECT COUNT(*) as count FROM users LIMIT 1');
            $tests['count_query'] = [
                'time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'status' => 'passed'
            ];
        } catch (\Exception $e) {
            $tests['count_query'] = [
                'time_ms' => null,
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
        
        // Test 3: Transaction test
        try {
            $startTime = microtime(true);
            $connection->transaction(function () use ($connection) {
                $connection->select('SELECT 1 as test');
            });
            $tests['transaction'] = [
                'time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'status' => 'passed'
            ];
        } catch (\Exception $e) {
            $tests['transaction'] = [
                'time_ms' => null,
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
        
        return [
            'connection' => $connectionName,
            'timestamp' => now()->toISOString(),
            'tests' => $tests,
            'overall_status' => collect($tests)->every(fn($test) => $test['status'] === 'passed') ? 'passed' : 'failed'
        ];
    }

    /**
     * Clear monitoring cache
     */
    public function clearMonitoringCache(): void
    {
        // Clear connection stats cache
        $pattern = 'db_connections:*';
        Cache::flush(); // Consider more specific cache clearing in production
    }
}