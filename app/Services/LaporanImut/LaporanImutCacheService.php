<?php

namespace App\Services\LaporanImut;

use App\Support\CacheKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class LaporanImutCacheService
{
    protected CacheRepository $cache;

    public function __construct(?CacheRepository $cache = null)
    {
        $this->cache = $cache ?: Cache::getFacadeRoot();
    }

    /**
     * Cache expiration times in minutes
     */
    const CACHE_LATEST_LAPORAN = 30;
    const CACHE_DASHBOARD_CHART = 10080; // 7 days
    const CACHE_RECENT_LAPORAN = 720; // 12 hours
    const CACHE_DASHBOARD_DATA = 720; // 12 hours

    /**
     * Get latest laporan from cache or store result
     */
    public function getCachedLatestLaporan(callable $callback)
    {
        return $this->cache->remember(
            CacheKey::latestLaporan(),
            now()->addMinutes(self::CACHE_LATEST_LAPORAN),
            $callback
        );
    }

    /**
     * Get dashboard chart data from cache or store result
     */
    public function getCachedChartData(callable $callback, int $limit = 6)
    {
        $cacheKey = CacheKey::dashboardSiimutChartData();

        return $this->cache->remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_DASHBOARD_CHART),
            $callback
        );
    }

    /**
     * Get recent laporan list from cache or store result
     */
    public function getCachedRecentLaporanList(callable $callback, int $limit)
    {
        return $this->cache->remember(
            CacheKey::recentLaporanList($limit),
            now()->addMinutes(self::CACHE_RECENT_LAPORAN),
            $callback
        );
    }

    /**
     * Get dashboard data for specific laporan from cache or store result
     */
    public function getCachedLaporanData(int $laporanId, callable $callback)
    {
        $cacheKey = CacheKey::dashboardSiimutAllData($laporanId);

        return $this->cache->remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_DASHBOARD_DATA),
            $callback
        );
    }

    /**
     * Clear all laporan related caches
     */
    public function clearLaporanCaches(?int $laporanId = null): void
    {
        // Clear general caches
        $this->cache->forget(CacheKey::latestLaporan());
        $this->cache->forget(CacheKey::dashboardSiimutChartData());

        // Clear specific laporan cache if provided
        if ($laporanId) {
            $this->cache->forget(CacheKey::dashboardSiimutAllData($laporanId));
        }

        // Clear recent laporan cache (multiple limits)
        for ($limit = 1; $limit <= 10; $limit++) {
            $this->cache->forget(CacheKey::recentLaporanList($limit));
        }
    }

    /**
     * Clear cache when laporan is updated
     */
    public function clearCacheOnLaporanUpdate(int $laporanId): void
    {
        $this->clearLaporanCaches($laporanId);
    }

    /**
     * Clear cache when penilaian is updated
     */
    public function clearCacheOnPenilaianUpdate(): void
    {
        $this->clearLaporanCaches();
    }

    /**
     * Get or set cache with custom expiration
     */
    public function remember(string $key, int $minutes, callable $callback)
    {
        return $this->cache->remember($key, now()->addMinutes($minutes), $callback);
    }

    /**
     * Flush specific cache pattern
     */
    public function forgetPattern(string $pattern): void
    {
        // For Redis cache driver, you could implement pattern-based cache clearing
        // For now, we'll implement basic forget
        $this->cache->forget($pattern);
    }
}
