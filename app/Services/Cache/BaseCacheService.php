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
            $taggedCache = $this->getServiceTaggedCache();
            if ($taggedCache !== null) {
                $result = $taggedCache->remember($cacheKey, $ttl, $callback);
                $this->logCacheMetrics('remember', $cacheKey, true);
                return $result;
            }

            $result = Cache::store($this->store)->remember($cacheKey, $ttl, $callback);
            $this->logCacheMetrics('remember', $cacheKey, true);
            return $result;
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
     * Get tagged cache instance with proper type handling
     */
    protected function getTaggedCacheInstance(array $tags): ?\Illuminate\Cache\TaggedCache
    {
        if (!$this->doesSupportTagging() || empty($tags)) {
            return null;
        }

        $store = Cache::store($this->store);

        // Use dynamic call to avoid IDE type checking issues
        if (method_exists($store, 'tags')) {
            return call_user_func([$store, 'tags'], $tags);
        }

        return null;
    }

    /**
     * Get tagged cache for current service tags
     */
    protected function getServiceTaggedCache(): ?\Illuminate\Cache\TaggedCache
    {
        return $this->getTaggedCacheInstance($this->tags);
    }

    /**
     * Put a value in cache with specific tags
     */
    public function putWithTags(array $tags, string $key, mixed $value, ?int $ttl = null): bool
    {
        $cacheKey = $this->generateKey($key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        try {
            $taggedCache = $this->getTaggedCacheInstance($tags);
            if ($taggedCache !== null) {
                $result = $taggedCache->put($cacheKey, $value, $ttl);
                $this->logCacheMetrics('put_tagged', $cacheKey);
                return $result;
            }

            // Fallback to regular put if tagging not supported
            return $this->put($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::warning('Cache putWithTags failed', [
                'key' => $cacheKey,
                'tags' => $tags,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return false;
        }
    }

    /**
     * Get a value from cache using specific tags
     */
    public function getWithTags(array $tags, string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->generateKey($key);

        try {
            $taggedCache = $this->getTaggedCacheInstance($tags);
            if ($taggedCache !== null) {
                $result = $taggedCache->get($cacheKey, $default);
                $this->logCacheMetrics('get_tagged', $cacheKey, $result !== $default);
                return $result;
            }

            // Fallback to regular get if tagging not supported
            return $this->get($key, $default);
        } catch (\Exception $e) {
            Log::warning('Cache getWithTags failed', [
                'key' => $cacheKey,
                'tags' => $tags,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return $default;
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
            $taggedCache = $this->getServiceTaggedCache();
            if ($taggedCache !== null) {
                $result = $taggedCache->put($cacheKey, $value, $ttl);
                $this->logCacheMetrics('put', $cacheKey);
                return $result;
            }

            $result = Cache::store($this->store)->put($cacheKey, $value, $ttl);
            $this->logCacheMetrics('put', $cacheKey);
            return $result;
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
            $taggedCache = $this->getServiceTaggedCache();
            if ($taggedCache !== null) {
                $result = $taggedCache->get($cacheKey, $default);
                $this->logCacheMetrics('get', $cacheKey, $result !== $default);
                return $result;
            }

            $result = Cache::store($this->store)->get($cacheKey, $default);
            $this->logCacheMetrics('get', $cacheKey, $result !== $default);
            return $result;
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
            $taggedCache = $this->getServiceTaggedCache();
            if ($taggedCache !== null) {
                return $taggedCache->forget($cacheKey);
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
            $taggedCache = $this->getServiceTaggedCache();
            if ($taggedCache !== null) {
                $taggedCache->flush();
                $this->logCacheMetrics('flush', 'all_tags');
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
     * Flush cache by specific tags
     * Following Laravel docs pattern: Cache::tags(['people', 'authors'])->flush();
     */
    public function flushByTags(array $tags): bool
    {
        try {
            $taggedCache = $this->getTaggedCacheInstance($tags);
            if ($taggedCache !== null) {
                $taggedCache->flush();

                $this->logCacheMetrics('flush_by_tags', implode(',', $tags));

                Log::info('Cache flushed by tags', [
                    'tags' => $tags,
                    'service' => static::class
                ]);

                return true;
            }

            Log::warning('Cannot flush cache by tags - tagging not supported', [
                'tags' => $tags,
                'service' => static::class
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Cache flush by tags failed', [
                'tags' => $tags,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return false;
        }
    }

    /**
     * Flush cache for a single tag
     * Following Laravel docs pattern: Cache::tags('authors')->flush();
     */
    public function flushByTag(string $tag): bool
    {
        return $this->flushByTags([$tag]);
    }

    /**
     * Check if the current cache store supports tagging (public method)
     */
    public function supportsTagging(): bool
    {
        return $this->doesSupportTagging();
    }

    /**
     * Check if the current cache store supports tagging (internal)
     */
    protected function doesSupportTagging(): bool
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
     * Get cache instance with tags (following Laravel docs pattern)
     * Examples:
     * - Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
     * - Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
     */
    protected function getCacheWithTags(array $tags = null): \Illuminate\Contracts\Cache\Repository
    {
        $cacheInstance = Cache::store($this->store);
        $tagsToUse = $tags ?? $this->tags;

        $taggedCache = $this->getTaggedCacheInstance($tagsToUse);
        if ($taggedCache !== null) {
            return $taggedCache;
        }

        return $cacheInstance;
    }

    /**
     * Cache data using tagged approach (Laravel docs pattern)
     * Usage: $this->cacheTaggedData(['people', 'artists'], 'John', $john, $seconds);
     */
    protected function cacheTaggedData(array $tags, string $key, mixed $value, ?int $ttl = null): bool
    {
        $cacheKey = $this->generateKey($key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        try {
            $taggedCache = $this->getTaggedCacheInstance($tags);
            if ($taggedCache !== null) {
                // Following Laravel docs: Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
                $result = $taggedCache->put($cacheKey, $value, $ttl);

                $this->logCacheMetrics('cache_tagged', $cacheKey);

                Log::debug('Tagged cache stored', [
                    'key' => $cacheKey,
                    'tags' => $tags,
                    'service' => static::class
                ]);

                return $result;
            }

            // Fallback to regular caching
            return $this->put($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::warning('Tagged cache storage failed', [
                'key' => $cacheKey,
                'tags' => $tags,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return false;
        }
    }

    /**
     * Retrieve data using tagged approach (Laravel docs pattern)
     * Usage: $john = $this->getTaggedData(['people', 'artists'], 'John');
     */
    protected function getTaggedData(array $tags, string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->generateKey($key);

        try {
            $taggedCache = $this->getTaggedCacheInstance($tags);
            if ($taggedCache !== null) {
                // Following Laravel docs: $john = Cache::tags(['people', 'artists'])->get('John');
                $result = $taggedCache->get($cacheKey, $default);

                $this->logCacheMetrics('get_tagged', $cacheKey, $result !== $default);
                return $result;
            }

            // Fallback to regular get
            return $this->get($key, $default);
        } catch (\Exception $e) {
            Log::warning('Tagged cache retrieval failed', [
                'key' => $cacheKey,
                'tags' => $tags,
                'error' => $e->getMessage(),
                'service' => static::class
            ]);

            return $default;
        }
    }

    /**
     * Log cache operation metrics
     */
    protected function logCacheMetrics(string $operation, string $key, ?bool $hit = null): void
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
