<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Simple Cache Optimization Service
 * Basic cache management for internal company app
 */
class SimpleCacheOptimizer
{
    /**
     * Cache prefixes for different data types
     */
    protected array $prefixes = [
        'user' => 'usr:',
        'laporan' => 'lap:',
        'unit_kerja' => 'uk:',
        'imut_data' => 'imd:',
        'settings' => 'set:',
    ];

    /**
     * Default cache durations (in minutes)
     */
    protected array $durations = [
        'short' => 15,    // 15 minutes
        'medium' => 60,   // 1 hour
        'long' => 1440,   // 24 hours
        'static' => 10080, // 7 days
    ];

    /**
     * Cache frequently accessed data
     */
    public function warmUpCache(): array
    {
        $results = [];

        try {
            // Warm up settings
            $results['settings'] = $this->warmUpSettings();

            // Warm up active unit kerja
            $results['unit_kerja'] = $this->warmUpUnitKerja();

            // Warm up latest laporan
            $results['laporan'] = $this->warmUpLatestLaporan();

            Log::info('Cache warm-up completed', $results);

        } catch (\Exception $e) {
            Log::error('Cache warm-up failed', ['error' => $e->getMessage()]);
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Warm up system settings
     */
    protected function warmUpSettings(): bool
    {
        $key = $this->getCacheKey('settings', 'system');

        if (!Cache::has($key)) {
            // Skip settings cache for now - can be implemented later
            Log::info('Settings cache skipped - implement if needed');
            return false;
        }

        return false;
    }

    /**
     * Warm up active unit kerja
     */
    protected function warmUpUnitKerja(): bool
    {
        $key = $this->getCacheKey('unit_kerja', 'active');

        if (!Cache::has($key)) {
            // Use correct column names based on actual table structure
            $unitKerjas = \App\Models\UnitKerja::select('id', 'unit_name', 'slug')
                ->whereNotNull('unit_name')
                ->get();
            Cache::put($key, $unitKerjas, $this->durations['medium']);
            return true;
        }

        return false;
    }

    /**
     * Warm up latest laporan
     */
    protected function warmUpLatestLaporan(): bool
    {
        $key = $this->getCacheKey('laporan', 'latest');

        if (!Cache::has($key)) {
            $laporan = \App\Models\LaporanImut::latest()->first();
            if ($laporan) {
                Cache::put($key, $laporan, $this->durations['medium']);
                return true;
            }
        }

        return false;
    }

    /**
     * Clear stale cache entries
     */
    public function clearStaleCache(): array
    {
        $results = [];

        try {
            // Clear old user sessions
            $results['user_sessions'] = $this->clearPatternCache('usr:session:*');

            // Clear old temporary data
            $results['temp_data'] = $this->clearPatternCache('tmp:*');

            Log::info('Stale cache cleanup completed', $results);

        } catch (\Exception $e) {
            Log::error('Cache cleanup failed', ['error' => $e->getMessage()]);
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Clear cache by pattern (Redis only)
     */
    protected function clearPatternCache(string $pattern): int
    {
        if (config('cache.default') === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                return $redis->del($keys);
            }
        }

        return 0;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $stats = [
            'driver' => config('cache.default'),
            'status' => 'unknown',
        ];

        try {
            // Test cache functionality
            $testKey = 'cache_test_' . time();
            Cache::put($testKey, 'test', 1);

            if (Cache::get($testKey) === 'test') {
                $stats['status'] = 'working';
                Cache::forget($testKey);
            }

            // Get Redis stats if using Redis
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $info = $redis->info();

                $stats['memory_used'] = $info['used_memory_human'] ?? 'unknown';
                $stats['connected_clients'] = $info['connected_clients'] ?? 'unknown';
                $stats['total_commands'] = $info['total_commands_processed'] ?? 'unknown';
            }

        } catch (\Exception $e) {
            $stats['status'] = 'error';
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }

    /**
     * Generate cache key with prefix
     */
    public function getCacheKey(string $type, string $identifier): string
    {
        $prefix = $this->prefixes[$type] ?? 'app:';
        return $prefix . $identifier;
    }

    /**
     * Cache with automatic duration selection
     */
    public function cacheRemember(string $type, string $identifier, callable $callback, string $duration = 'medium')
    {
        $key = $this->getCacheKey($type, $identifier);
        $ttl = $this->durations[$duration] ?? $this->durations['medium'];

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by type
     */
    public function invalidateByType(string $type): void
    {
        $prefix = $this->prefixes[$type] ?? 'app:';

        if (config('cache.default') === 'redis') {
            $this->clearPatternCache($prefix . '*');
        } else {
            // For other drivers, we'll need to track keys manually
            // This is a simplified approach
            Log::info("Cache invalidation requested for type: {$type}");
        }
    }
}
