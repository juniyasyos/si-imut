<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Base Cache Service
 *
 * Provides common caching functionality with consistent patterns,
 * key management, and invalidation strategies.
 */
abstract class BaseCacheService
{
    /**
     * Default cache TTL in seconds (1 hour)
     */
    protected const DEFAULT_TTL = 3600;

    /**
     * Cache key prefix for this service
     */
    protected string $keyPrefix;

    /**
     * Cache store to use
     */
    protected string $store;

    /**
     * Tags for cache invalidation
     */
    protected array $tags = [];

    public function __construct()
    {
        $this->store = config('cache.default', 'array');
        $this->keyPrefix = $this->getKeyPrefix();
        $this->tags = $this->getCacheTags();
    }

    /**
     * Get the cache key prefix for this service
     */
    abstract protected function getKeyPrefix(): string;

    /**
     * Get cache tags for this service
     */
    abstract protected function getCacheTags(): array;

    /**
     * Generate a cache key with prefix
     */
    protected function generateKey(string $key): string
    {
        return $this->keyPrefix . ':' . $key;
    }

    /**
     * Remember a value in cache with automatic key generation
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->generateKey($key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        try {
            if ($this->supportsTagging() && !empty($this->tags)) {
                return Cache::store($this->store)
                    ->tags($this->tags)
                    ->remember($cacheKey, $ttl, $callback);
            }

            return Cache::store($this->store)->remember($cacheKey, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning('Cache remember failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            // Fallback to direct callback execution
            return $callback();
        }
    }

    /**
     * Put a value in cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $cacheKey = $this->generateKey($key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        try {
            if ($this->supportsTagging() && !empty($this->tags)) {
                return Cache::store($this->store)
                    ->tags($this->tags)
                    ->put($cacheKey, $value, $ttl);
            }

            return Cache::store($this->store)->put($cacheKey, $value, $ttl);
        } catch (\Exception $e) {
            Log::warning('Cache put failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return false;
        }
    }

    /**
     * Get a value from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->generateKey($key);

        try {
            if ($this->supportsTagging() && !empty($this->tags)) {
                return Cache::store($this->store)
                    ->tags($this->tags)
                    ->get($cacheKey, $default);
            }

            return Cache::store($this->store)->get($cacheKey, $default);
        } catch (\Exception $e) {
            Log::warning('Cache get failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return $default;
        }
    }

    /**
     * Forget a specific cache key
     */
    public function forget(string $key): bool
    {
        $cacheKey = $this->generateKey($key);

        try {
            if ($this->supportsTagging() && !empty($this->tags)) {
                return Cache::store($this->store)
                    ->tags($this->tags)
                    ->forget($cacheKey);
            }

            return Cache::store($this->store)->forget($cacheKey);
        } catch (\Exception $e) {
            Log::warning('Cache forget failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return false;
        }
    }

    /**
     * Flush all cache for this service's tags
     */
    public function flush(): bool
    {
        try {
            if ($this->supportsTagging() && !empty($this->tags)) {
                Cache::store($this->store)->tags($this->tags)->flush();
                return true;
            }

            // If no tags, we can't safely flush everything
            Log::warning('Cannot flush cache without tags', [
                'service' => static::class
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Cache flush failed', [
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return false;
        }
    }

    /**
     * Check if the current cache store supports tagging
     */
    protected function supportsTagging(): bool
    {
        // Check if store type supports tagging
        if (!in_array($this->store, ['redis', 'memcached', 'array'])) {
            return false;
        }

        // If redis is configured but Redis class doesn't exist, return false
        if ($this->store === 'redis' && !class_exists('Redis')) {
            return false;
        }

        // Check if cache is actually available
        return $this->isCacheAvailable();
    }

    /**
     * Check if cache is available
     */
    protected function isCacheAvailable(): bool
    {
        try {
            // Try a simple cache operation
            $testKey = 'health_check_' . uniqid();
            $testValue = 'test';

            Cache::store($this->store)->put($testKey, $testValue, 1);
            $retrieved = Cache::store($this->store)->get($testKey);
            Cache::store($this->store)->forget($testKey);

            return $retrieved === $testValue;
        } catch (\Exception $e) {
            Log::warning('Cache unavailable', [
                'store' => $this->store,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Generate cache key for time-based invalidation
     */
    protected function generateTimeBasedKey(string $key, string $period = 'hour'): string
    {
        $timestamp = match($period) {
            'minute' => Carbon::now()->format('YmdHi'),
            'hour' => Carbon::now()->format('YmdH'),
            'day' => Carbon::now()->format('Ymd'),
            'week' => Carbon::now()->format('YW'),
            'month' => Carbon::now()->format('Ym'),
            default => Carbon::now()->format('YmdH')
        };

        return $key . ':' . $timestamp;
    }

    /**
     * Log cache operation metrics
     */
    protected function logCacheMetrics(string $operation, string $key, bool $hit = null): void
    {
        Log::debug('Cache operation', [
            'operation' => $operation,
            'key' => $key,
            'hit' => $hit,
            'service' => static::class,
            'store' => $this->store,
            'timestamp' => now()->toISOString()
        ]);
    }
}
