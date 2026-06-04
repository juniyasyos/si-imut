<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

class WarmPermissionCache extends Command
{
    protected $signature = 'cache:warm-permissions {--force : Force re-warming even if cache exists}';

    protected $description = 'Pre-warm permission cache to avoid cold-start penalty on bootstrap';

    public function handle(): int
    {
        $force = $this->option('force');
        $cacheKey = config('permission.cache.key');
        
        $this->info('🔥 Permission Cache Warming');
        $this->line('═══════════════════════════════════════════════════════════');

        // Check if cache already exists
        if (Cache::has($cacheKey) && !$force) {
            $this->info('✅ Permission cache already exists');
            return 0;
        }

        $this->info('📦 Loading permissions...');
        
        // This will trigger Spatie to cache all permissions
        // The key is to call load() which rebuilds the cache
        $start = microtime(true);
        
        try {
            // Force permission cache rebuild
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            
            // Load all permissions (this populates the cache)
            $permissions = Permission::all();
            $permCount = $permissions->count();
            
            $duration = (microtime(true) - $start) * 1000;
            
            $this->info("✅ Permission cache warmed");
            $this->line("   Permissions: {$permCount}");
            $this->line("   Duration: " . round($duration, 2) . "ms");
            $this->line("   Cache Key: {$cacheKey}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to warm permission cache: {$e->getMessage()}");
            return 1;
        }
    }
}
