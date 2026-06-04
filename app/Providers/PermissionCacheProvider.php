<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class PermissionCacheProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     * 
     * Warm permission cache on application boot to avoid cold-start penalty.
     * This ensures permissions are loaded efficiently from cache on every request.
     */
    public function boot(): void
    {
        // Only warm cache in production or when explicitly enabled
        if (!$this->shouldWarmCache()) {
            return;
        }

        try {
            $cacheKey = config('permission.cache.key');
            $ttl = config('permission.cache.expiration_time');
            
            // Check if cache exists and is still valid
            if (!Cache::has($cacheKey)) {
                // Cache is empty/expired - force warm
                $this->warmPermissionCache();
            }
        } catch (\Exception $e) {
            // Silently fail - don't break application if permission cache fails
            // Log for debugging in development
            if ($this->app->isLocal()) {
                \Illuminate\Support\Facades\Log::warning('Permission cache warming failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Determine if permission cache should be warmed
     */
    protected function shouldWarmCache(): bool
    {
        // Warm in production
        if ($this->app->isProduction()) {
            return true;
        }
        
        // Warm in testing with WARM_PERMISSION_CACHE=true
        if ($this->app->runningInConsole() && env('WARM_PERMISSION_CACHE') === true) {
            return true;
        }
        
        // Don't warm in local development (changes frequently)
        return false;
    }

    /**
     * Warm permission cache by loading all permissions
     */
    protected function warmPermissionCache(): void
    {
        // Force Spatie to rebuild cache
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Load all permissions (triggers cache population)
        Permission::all();
    }
}
