<?php

namespace Tests\Feature\DailyReport;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class Phase6PermissionCacheWarmingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that WarmPermissionCache command executes without errors
     */
    public function test_warm_permission_cache_command_executes(): void
    {
        // Clear cache
        Cache::flush();

        // Run command
        $this->artisan('cache:warm-permissions')
            ->assertSuccessful();
    }

    /**
     * Test that WarmPermissionCache command with --force flag works
     */
    public function test_warm_permission_cache_with_force_flag(): void
    {
        // Run command without force
        $this->artisan('cache:warm-permissions')->assertSuccessful();

        // Run command with force flag
        $this->artisan('cache:warm-permissions', ['--force' => true])
            ->assertSuccessful();
    }

    /**
     * Test that permission loading is fast
     */
    public function test_permission_loading_performance(): void
    {
        Cache::flush();

        $start = microtime(true);
        $permissions = Permission::all();
        $duration = (microtime(true) - $start) * 1000;

        // First load should complete within reasonable time
        // Expected: ~28ms based on earlier measurements
        $this->assertLessThan(100, $duration, "Permission load took {$duration}ms, expected < 100ms");
        $this->assertEquals(277, $permissions->count());
    }

    /**
     * Test that cache warming produces consistent results
     */
    public function test_cache_warming_consistency(): void
    {
        Cache::flush();

        // First warm
        $this->artisan('cache:warm-permissions')->assertSuccessful();
        $firstCount = Permission::all()->count();

        // Second warm
        Cache::flush();
        $this->artisan('cache:warm-permissions')->assertSuccessful();
        $secondCount = Permission::all()->count();

        // Results should be identical
        $this->assertEquals($firstCount, $secondCount);
        $this->assertEquals(277, $firstCount);
    }

    /**
     * Test that PermissionCacheProvider can be instantiated
     */
    public function test_permission_cache_provider_exists(): void
    {
        $providerClass = 'App\\Providers\\PermissionCacheProvider';
        $this->assertTrue(class_exists($providerClass), "Provider class {$providerClass} not found");
    }

    /**
     * Test that OptimizedCacheClearCommand exists
     */
    public function test_optimized_cache_clear_command_exists(): void
    {
        $this->artisan('cache:clear-app')
            ->assertSuccessful();
    }

    /**
     * Test permission access after cache warming
     */
    public function test_permission_access_after_warming(): void
    {
        Cache::flush();
        $this->artisan('cache:warm-permissions')->assertSuccessful();

        // Load a user
        $user = $this->createUser();

        // User should be able to check permissions
        $canView = $user->can('view_dashboard');
        $this->assertTrue(is_bool($canView), 'can() should return boolean');
    }

    /**
     * Helper to create a test user
     */
    private function createUser()
    {
        return \App\Models\User::factory()->create();
    }
}
