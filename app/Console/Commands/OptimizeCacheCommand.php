<?php

namespace App\Console\Commands;

use App\Services\Cache\SimpleCacheOptimizer;
use Illuminate\Console\Command;

/**
 * Simple Cache Optimization Command
 * Basic cache management for internal company app
 */
class OptimizeCacheCommand extends Command
{
    protected $signature = 'cache:simple-optimize
                          {--warmup : Warm up frequently accessed cache}
                          {--cleanup : Clean up stale cache entries}
                          {--stats : Show cache statistics}
                          {--all : Run all optimization tasks}';

    protected $description = 'Simple cache optimization for internal company app';

    protected SimpleCacheOptimizer $optimizer;

    public function __construct(SimpleCacheOptimizer $optimizer)
    {
        parent::__construct();
        $this->optimizer = $optimizer;
    }

    public function handle(): int
    {
        $this->info('🚀 Cache Optimization Tool');
        $this->line('');

        if ($this->option('all')) {
            return $this->runAllOptimizations();
        }

        if ($this->option('warmup')) {
            return $this->warmUpCache();
        }

        if ($this->option('cleanup')) {
            return $this->cleanupCache();
        }

        if ($this->option('stats')) {
            return $this->showCacheStats();
        }

        // Show help if no options provided
        $this->showHelp();
        return 0;
    }

    protected function runAllOptimizations(): int
    {
        $this->info('Running all cache optimizations...');
        $this->line('');

        $tasks = [
            'Cache Warm-up' => fn() => $this->warmUpCache(),
            'Cache Cleanup' => fn() => $this->cleanupCache(),
            'Cache Statistics' => fn() => $this->showCacheStats(),
        ];

        $results = [];
        foreach ($tasks as $taskName => $task) {
            $this->info("📋 {$taskName}");
            $result = $task();
            $results[] = $result === 0 ? '✅' : '❌';
            $this->line('');
        }

        $this->info('🎯 Optimization Summary:');
        foreach (array_keys($tasks) as $index => $taskName) {
            $this->line("  {$results[$index]} {$taskName}");
        }

        return in_array('❌', $results) ? 1 : 0;
    }

    protected function warmUpCache(): int
    {
        $this->info('🔥 Warming up cache...');

        try {
            $results = $this->optimizer->warmUpCache();

            foreach ($results as $type => $success) {
                if ($type === 'error') {
                    $this->error("❌ Error: {$success}");
                    continue;
                }

                $icon = $success ? '✅' : '⏭️';
                $status = $success ? 'warmed up' : 'already cached';
                $this->line("  {$icon} {$type}: {$status}");
            }

            $this->info('✅ Cache warm-up completed');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Cache warm-up failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function cleanupCache(): int
    {
        $this->info('🧹 Cleaning up stale cache...');

        try {
            $results = $this->optimizer->clearStaleCache();

            foreach ($results as $type => $count) {
                if ($type === 'error') {
                    $this->error("❌ Error: {$count}");
                    continue;
                }

                $this->line("  ✅ {$type}: {$count} entries cleared");
            }

            $this->info('✅ Cache cleanup completed');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Cache cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function showCacheStats(): int
    {
        $this->info('📊 Cache Statistics');
        $this->line('');

        try {
            $stats = $this->optimizer->getCacheStats();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Driver', $stats['driver']],
                    ['Status', $stats['status']],
                    ['Memory Used', $stats['memory_used'] ?? 'N/A'],
                    ['Connected Clients', $stats['connected_clients'] ?? 'N/A'],
                    ['Total Commands', $stats['total_commands'] ?? 'N/A'],
                ]
            );

            if ($stats['status'] === 'working') {
                $this->info('✅ Cache is functioning properly');
            } else {
                $this->warn('⚠️ Cache may have issues: ' . ($stats['error'] ?? 'Unknown'));
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to get cache stats: ' . $e->getMessage());
            return 1;
        }
    }

    protected function showHelp(): void
    {
        $this->info('Available options:');
        $this->line('  --warmup   Warm up frequently accessed cache');
        $this->line('  --cleanup  Clean up stale cache entries');
        $this->line('  --stats    Show cache statistics');
        $this->line('  --all      Run all optimization tasks');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan cache:optimize --warmup');
        $this->line('  php artisan cache:optimize --all');
    }
}
