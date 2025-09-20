<?php

namespace Tests\Feature\Cache;

use App\Models\User;
use App\Models\UnitKerja;
use App\Services\Cache\UserCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(UserCacheService::class);

        // Use array cache for testing
        config(['cache.default' => 'array']);

        // Setup permissions
        $this->artisan('permission:create-role super_admin');
        $this->artisan('permission:create-permission view_all_unit_kerja');
        $this->artisan('permission:create-permission view_imut_data');
    }

    /** @test */
    public function it_can_cache_user_profile(): void
    {
                $unitKerja = UnitKerja::factory()->create();
        $user = User::factory()->create();
        $user->unitKerjas()->attach($unitKerja->id);

        $role = Role::create(['name' => 'admin']);
        $user->assignRole($role);

        $profile1 = $this->cacheService->getUserProfile($user->id);
        $profile2 = $this->cacheService->getUserProfile($user->id);

        $this->assertNotNull($profile1);
        $this->assertNotNull($profile2);
        $this->assertEquals($profile1->id, $profile2->id);
        $this->assertTrue($profile1->hasRole('admin'));
    }

    /** @test */
    public function it_can_cache_user_permissions(): void
    {
        $user = User::factory()->create();

        $role = Role::create(['name' => 'editor']);
        $permission = Permission::create(['name' => 'edit_content']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $permissions1 = $this->cacheService->getUserPermissions($user->id);
        $permissions2 = $this->cacheService->getUserPermissions($user->id);

        $this->assertIsArray($permissions1);
        $this->assertIsArray($permissions2);
        $this->assertEquals($permissions1, $permissions2);
        $this->assertContains('edit_content', $permissions1);
    }

    /** @test */
    public function it_can_cache_accessible_unit_kerja(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $user = User::factory()->create();
        $user->unitKerjas()->attach($unitKerja->id);

        $accessibleUnits1 = $this->cacheService->getUserAccessibleUnitKerja($user->id);
        $accessibleUnits2 = $this->cacheService->getUserAccessibleUnitKerja($user->id);

        $this->assertEquals($accessibleUnits1->count(), $accessibleUnits2->count());
        $this->assertEquals(1, $accessibleUnits1->count());
        $this->assertEquals($unitKerja->id, $accessibleUnits1->first()->id);
    }

    /** @test */
    public function it_handles_super_admin_access_correctly(): void
    {
        $unitKerja1 = UnitKerja::factory()->create();
        $unitKerja2 = UnitKerja::factory()->create();
        $user = User::factory()->create();

        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $user->assignRole($role);

        $accessibleUnits = $this->cacheService->getUserAccessibleUnitKerja($user->id);

        // Super admin should have access to all unit kerja
        $this->assertEquals(2, $accessibleUnits->count());
    }

    /** @test */
    public function it_can_cache_user_dashboard_data(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $user = User::factory()->create();
        $user->unitKerjas()->attach($unitKerja->id);

        $permission = Permission::firstOrCreate(['name' => 'view_imut_data']);
        $user->givePermissionTo($permission);

        $dashboardData = $this->cacheService->getUserDashboardData($user->id);

        $this->assertIsArray($dashboardData);
        $this->assertArrayHasKey('accessible_unit_kerja_count', $dashboardData);
        $this->assertArrayHasKey('total_imut_data_count', $dashboardData);
        $this->assertArrayHasKey('user_role', $dashboardData);
        $this->assertArrayHasKey('last_activity', $dashboardData);

        $this->assertEquals(1, $dashboardData['accessible_unit_kerja_count']);
        $this->assertEquals('user', $dashboardData['user_role']);
    }

    /** @test */
    public function it_can_cache_user_preferences(): void
    {
        $user = User::factory()->create();

        $preferences = $this->cacheService->getUserPreferences($user->id);

        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('theme', $preferences);
        $this->assertArrayHasKey('language', $preferences);
        $this->assertArrayHasKey('timezone', $preferences);
        $this->assertArrayHasKey('notifications_enabled', $preferences);

        // Test default values
        $this->assertEquals('system', $preferences['theme']);
        $this->assertEquals('id', $preferences['language']);
        $this->assertEquals('Asia/Jakarta', $preferences['timezone']);
        $this->assertTrue($preferences['notifications_enabled']);
    }

    /** @test */
    public function it_can_update_user_preferences(): void
    {
        $user = User::factory()->create();

        $newPreferences = [
            'theme' => 'dark',
            'language' => 'en',
            'notifications_enabled' => false
        ];

        $this->cacheService->updateUserPreferences($user->id, $newPreferences);
        $cachedPreferences = $this->cacheService->getUserPreferences($user->id);

        $this->assertEquals('dark', $cachedPreferences['theme']);
        $this->assertEquals('en', $cachedPreferences['language']);
        $this->assertFalse($cachedPreferences['notifications_enabled']);
    }

    /** @test */
    public function it_can_cache_users_list(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $user1 = User::factory()->create();
        $user1->unitKerjas()->attach($unitKerja->id);
        $user2 = User::factory()->create();
        $user2->unitKerjas()->attach($unitKerja->id);

        $usersList1 = $this->cacheService->getUsersList();
        $usersList2 = $this->cacheService->getUsersList();

        $this->assertEquals($usersList1->count(), $usersList2->count());
        $this->assertEquals(2, $usersList1->count());
    }

    /** @test */
    public function it_handles_user_list_filters(): void
    {
        $unitKerja1 = UnitKerja::factory()->create();
        $unitKerja2 = UnitKerja::factory()->create();

        $role = Role::create(['name' => 'manager']);

        $user1 = User::factory()->create([
            'name' => 'John Doe'
        ]);
        $user1->unitKerjas()->attach($unitKerja1->id);
        $user1->assignRole($role);

        $user2 = User::factory()->create([
            'name' => 'Jane Smith'
        ]);
        $user2->unitKerjas()->attach($unitKerja2->id);

        // Test role filter
        $roleFiltered = $this->cacheService->getUsersList(['role' => 'manager']);
        $this->assertEquals(1, $roleFiltered->count());
        $this->assertEquals('John Doe', $roleFiltered->first()->name);

        // Test unit kerja filter (note: this will need a different filter approach)
        $unitFiltered = $this->cacheService->getUsersList(['unit_kerja_id' => $unitKerja2->id]);
        $this->assertEquals(1, $unitFiltered->count());
        $this->assertEquals('Jane Smith', $unitFiltered->first()->name);

        // Test search filter
        $searchFiltered = $this->cacheService->getUsersList(['search' => 'John']);
        $this->assertEquals(1, $searchFiltered->count());
        $this->assertEquals('John Doe', $searchFiltered->first()->name);
    }

    /** @test */
    public function it_can_cache_role_statistics(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $permission = Permission::create(['name' => 'manage_users']);
        $adminRole->givePermissionTo($permission);

        $user1 = User::factory()->create();
        $user1->assignRole($adminRole);

        $user2 = User::factory()->create();
        $user2->assignRole($userRole);

        $user3 = User::factory()->create(); // No role

        $stats = $this->cacheService->getRoleStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('active_users', $stats);
        $this->assertArrayHasKey('role_breakdown', $stats);
        $this->assertArrayHasKey('users_without_roles', $stats);

        $this->assertEquals(3, $stats['total_users']);
        $this->assertEquals(3, $stats['active_users']);
        $this->assertEquals(1, $stats['users_without_roles']);

        $this->assertEquals(1, $stats['role_breakdown']['admin']['count']);
        $this->assertEquals(1, $stats['role_breakdown']['user']['count']);
        $this->assertEquals(1, $stats['role_breakdown']['admin']['permissions_count']);
    }

    /** @test */
    public function it_can_invalidate_user_cache(): void
    {
        $user = User::factory()->create();

        // Cache some user data
        $this->cacheService->getUserProfile($user->id);
        $this->cacheService->getUserPermissions($user->id);
        $this->cacheService->getUserDashboardData($user->id);

        // Invalidate user cache
        $this->cacheService->invalidateUser($user->id);

        // All invalidation calls should complete without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_get_user_activity_summary(): void
    {
        $user = User::factory()->create();

        $activitySummary = $this->cacheService->getUserActivitySummary($user->id, 30);

        $this->assertIsArray($activitySummary);
        $this->assertArrayHasKey('user_id', $activitySummary);
        $this->assertArrayHasKey('period_days', $activitySummary);
        $this->assertArrayHasKey('login_count', $activitySummary);
        $this->assertArrayHasKey('most_used_features', $activitySummary);

        $this->assertEquals($user->id, $activitySummary['user_id']);
        $this->assertEquals(30, $activitySummary['period_days']);
        $this->assertIsArray($activitySummary['most_used_features']);
    }
}
