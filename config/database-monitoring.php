<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for database monitoring,
    | query optimization, and performance tracking.
    |
    */

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Enable Monitoring
        |--------------------------------------------------------------------------
        |
        | Set to true to enable database query monitoring and logging.
        | Should be false in production to avoid performance overhead.
        |
        */
        'enabled' => env('DB_MONITORING_ENABLED', !app()->environment('production')),

        /*
        |--------------------------------------------------------------------------
        | Slow Query Threshold
        |--------------------------------------------------------------------------
        |
        | Queries taking longer than this threshold (in milliseconds) will be
        | logged as slow queries for analysis.
        |
        */
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000),

        /*
        |--------------------------------------------------------------------------
        | Log Slow Queries
        |--------------------------------------------------------------------------
        |
        | Whether to log slow queries to the application log.
        |
        */
        'log_slow_queries' => env('DB_LOG_SLOW_QUERIES', true),

        /*
        |--------------------------------------------------------------------------
        | Cache Query Statistics
        |--------------------------------------------------------------------------
        |
        | Whether to cache query statistics for performance analysis.
        |
        */
        'cache_stats' => env('DB_CACHE_STATS', true),

        /*
        |--------------------------------------------------------------------------
        | Statistics Retention
        |--------------------------------------------------------------------------
        |
        | How long to keep query statistics in cache (in days).
        |
        */
        'stats_retention_days' => env('DB_STATS_RETENTION_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pool Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database connection pooling and optimization.
    |
    */
    'pool' => [
        /*
        |--------------------------------------------------------------------------
        | Pool Sizes
        |--------------------------------------------------------------------------
        |
        | Minimum and maximum number of connections in the pool.
        |
        */
        'min_connections' => env('DB_POOL_MIN', 1),
        'max_connections' => env('DB_POOL_MAX', 10),

        /*
        |--------------------------------------------------------------------------
        | Connection Timeouts
        |--------------------------------------------------------------------------
        |
        | Connection timeout settings in seconds.
        |
        */
        'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 60),
        'idle_timeout' => env('DB_IDLE_TIMEOUT', 300),

        /*
        |--------------------------------------------------------------------------
        | Health Check
        |--------------------------------------------------------------------------
        |
        | Configuration for connection health monitoring.
        |
        */
        'health_check_interval' => env('DB_HEALTH_CHECK_INTERVAL', 60), // seconds
        'health_check_timeout' => env('DB_HEALTH_CHECK_TIMEOUT', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for automatic query optimization and suggestions.
    |
    */
    'optimization' => [
        /*
        |--------------------------------------------------------------------------
        | Auto-optimize Connections
        |--------------------------------------------------------------------------
        |
        | Whether to automatically apply connection optimizations on startup.
        |
        */
        'auto_optimize' => env('DB_AUTO_OPTIMIZE', true),

        /*
        |--------------------------------------------------------------------------
        | Index Suggestions
        |--------------------------------------------------------------------------
        |
        | Whether to generate index suggestions for slow queries.
        |
        */
        'suggest_indexes' => env('DB_SUGGEST_INDEXES', true),

        /*
        |--------------------------------------------------------------------------
        | N+1 Query Detection
        |--------------------------------------------------------------------------
        |
        | Whether to detect and warn about potential N+1 query issues.
        |
        */
        'detect_n_plus_one' => env('DB_DETECT_N_PLUS_ONE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Testing
    |--------------------------------------------------------------------------
    |
    | Configuration for database performance testing.
    |
    */
    'performance' => [
        /*
        |--------------------------------------------------------------------------
        | Test Interval
        |--------------------------------------------------------------------------
        |
        | How often to run automated performance tests (in minutes).
        | Set to 0 to disable automated testing.
        |
        */
        'test_interval' => env('DB_PERFORMANCE_TEST_INTERVAL', 60),

        /*
        |--------------------------------------------------------------------------
        | Alert Thresholds
        |--------------------------------------------------------------------------
        |
        | Performance thresholds that trigger alerts.
        |
        */
        'alert_thresholds' => [
            'response_time_ms' => env('DB_ALERT_RESPONSE_TIME', 100),
            'slow_query_percentage' => env('DB_ALERT_SLOW_QUERY_PCT', 10),
            'connection_failures' => env('DB_ALERT_CONNECTION_FAILURES', 5),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database-related caching.
    |
    */
    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Query Result Caching
        |--------------------------------------------------------------------------
        |
        | Whether to enable automatic query result caching for read-only queries.
        |
        */
        'query_cache_enabled' => env('DB_QUERY_CACHE_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Cache TTL
        |--------------------------------------------------------------------------
        |
        | Default time-to-live for cached query results (in minutes).
        |
        */
        'default_ttl' => env('DB_CACHE_TTL', 60),

        /*
        |--------------------------------------------------------------------------
        | Cache Tags
        |--------------------------------------------------------------------------
        |
        | Whether to use cache tags for better cache invalidation.
        |
        */
        'use_tags' => env('DB_CACHE_USE_TAGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver-Specific Optimizations
    |--------------------------------------------------------------------------
    |
    | Database driver-specific optimization settings.
    |
    */
    'drivers' => [
        'mysql' => [
            'session_variables' => [
                'query_cache_type' => 'ON',
                'sort_buffer_size' => 2097152, // 2MB
                'join_buffer_size' => 1048576, // 1MB
                'read_buffer_size' => 1048576, // 1MB
                'bulk_insert_buffer_size' => 8388608, // 8MB
                'wait_timeout' => 28800, // 8 hours
                'interactive_timeout' => 28800,
                'tx_isolation' => 'READ-COMMITTED',
            ],
        ],

        'pgsql' => [
            'session_variables' => [
                'work_mem' => '16MB',
                'maintenance_work_mem' => '64MB',
                'random_page_cost' => 1.1,
                'effective_cache_size' => '256MB',
                'application_name' => 'si-imut-app',
                'timezone' => 'Asia/Jakarta',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup and Maintenance
    |--------------------------------------------------------------------------
    |
    | Configuration for database backup and maintenance operations.
    |
    */
    'maintenance' => [
        /*
        |--------------------------------------------------------------------------
        | Auto Vacuum/Optimize
        |--------------------------------------------------------------------------
        |
        | Whether to automatically run database optimization commands.
        |
        */
        'auto_optimize_tables' => env('DB_AUTO_OPTIMIZE_TABLES', false),

        /*
        |--------------------------------------------------------------------------
        | Maintenance Schedule
        |--------------------------------------------------------------------------
        |
        | Cron expression for when to run maintenance tasks.
        |
        */
        'schedule' => env('DB_MAINTENANCE_SCHEDULE', '0 2 * * 0'), // Sunday 2 AM

        /*
        |--------------------------------------------------------------------------
        | Index Analysis
        |--------------------------------------------------------------------------
        |
        | Whether to regularly analyze index usage and suggest optimizations.
        |
        */
        'analyze_indexes' => env('DB_ANALYZE_INDEXES', true),
    ],
];
