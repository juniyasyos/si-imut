<?php<?php<?php



use App\Models\User;

use App\Models\UnitKerja;

use App\Services\Cache\UserCacheService;use App\Models\User;use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Spatie\Permission\Models\Role;use App\Models\UnitKerja;use App\Models\UnitKerja;

use Spatie\Permission\Models\Permission;

use App\Services\Cache\UserCacheService;use App\Services\Cache\UserCacheService;

uses(RefreshDatabase::class);

use Illuminate\Foundation\Testing\RefreshDatabase;use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {

    $this->cacheService = app(UserCacheService::class);use Spatie\Permission\Models\Role;use Spatie\Permission\Models\Role;



    // Use array cache for testinguse Spatie\Permission\Models\Permission;use Spatie\Permission\Models\Permission;

    config(['cache.default' => 'array']);



    // Setup permissions

    $this->artisan('permission:create-role super_admin');uses(RefreshDatabase::class);uses(RefreshDatabase::class);

    $this->artisan('permission:create-permission view_all_unit_kerja');

    $this->artisan('permission:create-permission view_imut_data');

});

beforeEach(function () {beforeEach(function () {

test('it can cache user profile', function () {

    $unitKerja = UnitKerja::factory()->create();    $this->cacheService = app(UserCacheService::class);    $this->cacheService = app(UserCacheService::class);

    $user = User::factory()->create();

    $user->unitKerjas()->attach($unitKerja->id);



    $role = Role::create(['name' => 'admin']);    // Use array cache for testing    // Use array cache for testing

    $user->assignRole($role);

    config(['cache.default' => 'array']);    config(['cache.default' => 'array']);

    $profile1 = $this->cacheService->getUserProfile($user->id);

    $profile2 = $this->cacheService->getUserProfile($user->id);



    expect($profile1)->not()->toBeNull()    // Setup permissions    // Setup permissions

        ->and($profile2)->not()->toBeNull()

        ->and($profile1->id)->toBe($profile2->id)    $this->artisan('permission:create-role super_admin');    $this->artisan('permission:create-role super_admin');

        ->and($profile1->hasRole('admin'))->toBeTrue();

});    $this->artisan('permission:create-permission view_all_unit_kerja');    $this->artisan('permission:create-permission view_all_unit_kerja');



test('it can cache user permissions', function () {    $this->artisan('permission:create-permission view_imut_data');    $this->artisan('permission:create-permission view_imut_data');

    $user = User::factory()->create();

});});

    $role = Role::create(['name' => 'editor']);

    $permission = Permission::create(['name' => 'edit_content']);

    $role->givePermissionTo($permission);

    $user->assignRole($role);test('it can cache user profile', function () {test('it can cache user profile', function () {



    $permissions1 = $this->cacheService->getUserPermissions($user->id);    $unitKerja = UnitKerja::factory()->create();    $unitKerja = UnitKerja::factory()->create();

    $permissions2 = $this->cacheService->getUserPermissions($user->id);

    $user = User::factory()->create();    $user = User::factory()->create();

    expect($permissions1)->toBeArray()

        ->and($permissions2)->toBeArray()    $user->unitKerjas()->attach($unitKerja->id);    $user->unitKerjas()->attach($unitKerja->id);

        ->and($permissions1)->toBe($permissions2)

        ->and($permissions1)->toContain('edit_content');

});

    $role = Role::create(['name' => 'admin']);        $role = Role::create(['name' => 'admin']);

test('it can cache accessible unit kerja', function () {

    $unitKerja = UnitKerja::factory()->create();    $user->assignRole($role);        $user->assignRole($role);

    $user = User::factory()->create();

    $user->unitKerjas()->attach($unitKerja->id);



    $accessibleUnits1 = $this->cacheService->getUserAccessibleUnitKerja($user->id);    $profile1 = $this->cacheService->getUserProfile($user->id);        $profile1 = $this->cacheService->getUserProfile($user->id);

    $accessibleUnits2 = $this->cacheService->getUserAccessibleUnitKerja($user->id);

    $profile2 = $this->cacheService->getUserProfile($user->id);        $profile2 = $this->cacheService->getUserProfile($user->id);

    expect($accessibleUnits1->count())->toBe($accessibleUnits2->count())

        ->and($accessibleUnits1->count())->toBe(1)

        ->and($accessibleUnits1->first()->id)->toBe($unitKerja->id);

});    expect($profile1)->not()->toBeNull()        $this->assertNotNull($profile1);



test('it handles super admin access correctly', function () {        ->and($profile2)->not()->toBeNull()        $this->assertNotNull($profile2);

    $unitKerja1 = UnitKerja::factory()->create();

    $unitKerja2 = UnitKerja::factory()->create();        ->and($profile1->id)->toBe($profile2->id)        $this->assertEquals($profile1->id, $profile2->id);

    $user = User::factory()->create();

        ->and($profile1->hasRole('admin'))->toBeTrue();        $this->assertTrue($profile1->hasRole('admin'));

    $role = Role::create(['name' => 'super_admin']);

    $user->assignRole($role);});    }



    $accessibleUnits = $this->cacheService->getUserAccessibleUnitKerja($user->id);



    expect($accessibleUnits->count())->toBe(2);test('it can cache user permissions', function () {    #[Test]

});

    $user = User::factory()->create();    public function it_can_cache_user_permissions(): void

test('it can cache user dashboard data', function () {

    $unitKerja = UnitKerja::factory()->create();    {

    $user = User::factory()->create();

    $user->unitKerjas()->attach($unitKerja->id);    $role = Role::create(['name' => 'editor']);        $user = User::factory()->create();



    $role = Role::create(['name' => 'user']);    $permission = Permission::create(['name' => 'edit_content']);

    $user->assignRole($role);

    $role->givePermissionTo($permission);        $role = Role::create(['name' => 'editor']);

    $dashboardData1 = $this->cacheService->getUserDashboardData($user->id);

    $dashboardData2 = $this->cacheService->getUserDashboardData($user->id);    $user->assignRole($role);        $permission = Permission::create(['name' => 'edit_content']);



    expect($dashboardData1)->toBeArray()        $role->givePermissionTo($permission);

        ->and($dashboardData2)->toBeArray()

        ->and($dashboardData1)->toBe($dashboardData2)    $permissions1 = $this->cacheService->getUserPermissions($user->id);        $user->assignRole($role);

        ->and($dashboardData1)->toHaveKeys(['user', 'roles', 'permissions', 'accessible_units']);

});    $permissions2 = $this->cacheService->getUserPermissions($user->id);



test('it can cache user preferences', function () {        $permissions1 = $this->cacheService->getUserPermissions($user->id);

    $user = User::factory()->create();

    expect($permissions1)->toBeArray()        $permissions2 = $this->cacheService->getUserPermissions($user->id);

    $preferences1 = $this->cacheService->getUserPreferences($user->id);

    $preferences2 = $this->cacheService->getUserPreferences($user->id);        ->and($permissions2)->toBeArray()



    expect($preferences1)->toBeArray()        ->and($permissions1)->toBe($permissions2)        $this->assertIsArray($permissions1);

        ->and($preferences2)->toBeArray()

        ->and($preferences1)->toBe($preferences2)        ->and($permissions1)->toContain('edit_content');        $this->assertIsArray($permissions2);

        ->and($preferences1)->toHaveKeys(['theme', 'notifications', 'dashboard_layout']);

});});        $this->assertEquals($permissions1, $permissions2);



test('it can update user preferences', function () {        $this->assertContains('edit_content', $permissions1);

    $user = User::factory()->create();

test('it can cache accessible unit kerja', function () {    }

    $initialPrefs = $this->cacheService->getUserPreferences($user->id);

        $unitKerja = UnitKerja::factory()->create();

    $newPrefs = ['theme' => 'dark', 'notifications' => false];

    $result = $this->cacheService->updateUserPreferences($user->id, $newPrefs);    $user = User::factory()->create();    #[Test]



    $cachedPrefs = $this->cacheService->getUserPreferences($user->id);    $user->unitKerjas()->attach($unitKerja->id);    public function it_can_cache_accessible_unit_kerja(): void



    expect($result)->toBeTrue()    {

        ->and($cachedPrefs['theme'])->toBe('dark')

        ->and($cachedPrefs['notifications'])->toBeFalse();    $accessibleUnits1 = $this->cacheService->getUserAccessibleUnitKerja($user->id);        $unitKerja = UnitKerja::factory()->create();

});

    $accessibleUnits2 = $this->cacheService->getUserAccessibleUnitKerja($user->id);        $user = User::factory()->create();

test('it can cache users list', function () {

    User::factory()->count(3)->create();        $user->unitKerjas()->attach($unitKerja->id);



    $usersList1 = $this->cacheService->getUsersList();    expect($accessibleUnits1->count())->toBe($accessibleUnits2->count())

    $usersList2 = $this->cacheService->getUsersList();

        ->and($accessibleUnits1->count())->toBe(1)        $accessibleUnits1 = $this->cacheService->getUserAccessibleUnitKerja($user->id);

    expect($usersList1)->toBeInstanceOf('Illuminate\Database\Eloquent\Collection')

        ->and($usersList2)->toBeInstanceOf('Illuminate\Database\Eloquent\Collection')        ->and($accessibleUnits1->first()->id)->toBe($unitKerja->id);        $accessibleUnits2 = $this->cacheService->getUserAccessibleUnitKerja($user->id);

        ->and($usersList1->count())->toBe($usersList2->count())

        ->and($usersList1->count())->toBeGreaterThan(0);});

});

        $this->assertEquals($accessibleUnits1->count(), $accessibleUnits2->count());

test('it handles user list filters', function () {

    $unitKerja1 = UnitKerja::factory()->create(['nama' => 'Unit A']);test('it handles super admin access correctly', function () {        $this->assertEquals(1, $accessibleUnits1->count());

    $unitKerja2 = UnitKerja::factory()->create(['nama' => 'Unit B']);

    $unitKerja1 = UnitKerja::factory()->create();        $this->assertEquals($unitKerja->id, $accessibleUnits1->first()->id);

    $user1 = User::factory()->create(['name' => 'User A']);

    $user2 = User::factory()->create(['name' => 'User B']);    $unitKerja2 = UnitKerja::factory()->create();    }

    $user3 = User::factory()->create(['name' => 'User C']);

    $user = User::factory()->create();

    $user1->unitKerjas()->attach($unitKerja1->id);

    $user2->unitKerjas()->attach($unitKerja2->id);    #[Test]

    $user3->unitKerjas()->attach($unitKerja1->id);

    $role = Role::create(['name' => 'super_admin']);    public function it_handles_super_admin_access_correctly(): void

    $role1 = Role::create(['name' => 'manager']);

    $role2 = Role::create(['name' => 'staff']);    $user->assignRole($role);    {



    $user1->assignRole($role1);        $unitKerja1 = UnitKerja::factory()->create();

    $user2->assignRole($role2);

    $user3->assignRole($role1);    $accessibleUnits = $this->cacheService->getUserAccessibleUnitKerja($user->id);        $unitKerja2 = UnitKerja::factory()->create();



    // Test filter by unit        $user = User::factory()->create();

    $filteredByUnit1 = $this->cacheService->getUsersByUnitKerja($unitKerja1->id);

    $filteredByUnit2 = $this->cacheService->getUsersByUnitKerja($unitKerja2->id);    expect($accessibleUnits->count())->toBe(2);



    expect($filteredByUnit1->count())->toBe(2)});        $role = Role::firstOrCreate(['name' => 'super_admin']);

        ->and($filteredByUnit2->count())->toBe(1);

        $user->assignRole($role);

    // Test filter by role

    $filteredByRole1 = $this->cacheService->getUsersByRole('manager');test('it can cache user dashboard data', function () {

    $filteredByRole2 = $this->cacheService->getUsersByRole('staff');

    $unitKerja = UnitKerja::factory()->create();        $accessibleUnits = $this->cacheService->getUserAccessibleUnitKerja($user->id);

    expect($filteredByRole1->count())->toBe(2)

        ->and($filteredByRole2->count())->toBe(1);    $user = User::factory()->create();



    // Test combined filter    $user->unitKerjas()->attach($unitKerja->id);        // Super admin should have access to all unit kerja

    $combinedFilter = $this->cacheService->getUsersByRoleAndUnit('manager', $unitKerja1->id);

            $this->assertEquals(2, $accessibleUnits->count());

    expect($combinedFilter->count())->toBe(2);

});    $role = Role::create(['name' => 'user']);    }



test('it can cache role statistics', function () {    $user->assignRole($role);

    $role1 = Role::create(['name' => 'admin']);

    $role2 = Role::create(['name' => 'user']);    #[Test]



    $user1 = User::factory()->create();    $dashboardData1 = $this->cacheService->getUserDashboardData($user->id);    public function it_can_cache_user_dashboard_data(): void

    $user2 = User::factory()->create();

    $user3 = User::factory()->create();    $dashboardData2 = $this->cacheService->getUserDashboardData($user->id);    {



    $user1->assignRole($role1);        $unitKerja = UnitKerja::factory()->create();

    $user2->assignRole($role2);

    $user3->assignRole($role2);    expect($dashboardData1)->toBeArray()        $user = User::factory()->create();



    $stats1 = $this->cacheService->getRoleStatistics();        ->and($dashboardData2)->toBeArray()        $user->unitKerjas()->attach($unitKerja->id);

    $stats2 = $this->cacheService->getRoleStatistics();

        ->and($dashboardData1)->toBe($dashboardData2)

    expect($stats1)->toBeArray()

        ->and($stats2)->toBeArray()        ->and($dashboardData1)->toHaveKeys(['user', 'roles', 'permissions', 'accessible_units']);        $permission = Permission::firstOrCreate(['name' => 'view_imut_data']);

        ->and($stats1)->toBe($stats2)

        ->and($stats1)->toHaveKeys(['admin', 'user'])});        $user->givePermissionTo($permission);

        ->and($stats1['admin'])->toBe(1)

        ->and($stats1['user'])->toBe(2);

});

test('it can cache user preferences', function () {        $dashboardData = $this->cacheService->getUserDashboardData($user->id);

test('it can invalidate user cache', function () {

    $user = User::factory()->create();    $user = User::factory()->create();



    // Cache some data        $this->assertIsArray($dashboardData);

    $profile1 = $this->cacheService->getUserProfile($user->id);

    $permissions1 = $this->cacheService->getUserPermissions($user->id);    $preferences1 = $this->cacheService->getUserPreferences($user->id);        $this->assertArrayHasKey('accessible_unit_kerja_count', $dashboardData);



    // Invalidate cache    $preferences2 = $this->cacheService->getUserPreferences($user->id);        $this->assertArrayHasKey('total_imut_data_count', $dashboardData);

    $result = $this->cacheService->invalidateUserCache($user->id);

        $this->assertArrayHasKey('user_role', $dashboardData);

    expect($result)->toBeTrue();

    expect($preferences1)->toBeArray()        $this->assertArrayHasKey('last_activity', $dashboardData);

    // Verify cache is cleared by checking if new data is fetched

    $profile2 = $this->cacheService->getUserProfile($user->id);        ->and($preferences2)->toBeArray()



    expect($profile2)->not()->toBeNull();        ->and($preferences1)->toBe($preferences2)        $this->assertEquals(1, $dashboardData['accessible_unit_kerja_count']);

});

        ->and($preferences1)->toHaveKeys(['theme', 'notifications', 'dashboard_layout']);        $this->assertEquals('user', $dashboardData['user_role']);

test('it can get user activity summary', function () {

    $user = User::factory()->create();});    }



    $summary1 = $this->cacheService->getUserActivitySummary($user->id);

    $summary2 = $this->cacheService->getUserActivitySummary($user->id);

test('it can update user preferences', function () {    #[Test]

    expect($summary1)->toBeArray()

        ->and($summary2)->toBeArray()    $user = User::factory()->create();    public function it_can_cache_user_preferences(): void

        ->and($summary1)->toBe($summary2)

        ->and($summary1)->toHaveKeys(['last_login', 'total_logins', 'activities_count']);    {

});
    $initialPrefs = $this->cacheService->getUserPreferences($user->id);        $user = User::factory()->create();



    $newPrefs = ['theme' => 'dark', 'notifications' => false];        $preferences = $this->cacheService->getUserPreferences($user->id);

    $result = $this->cacheService->updateUserPreferences($user->id, $newPrefs);

        $this->assertIsArray($preferences);

    $cachedPrefs = $this->cacheService->getUserPreferences($user->id);        $this->assertArrayHasKey('theme', $preferences);

        $this->assertArrayHasKey('language', $preferences);

    expect($result)->toBeTrue()        $this->assertArrayHasKey('timezone', $preferences);

        ->and($cachedPrefs['theme'])->toBe('dark')        $this->assertArrayHasKey('notifications_enabled', $preferences);

        ->and($cachedPrefs['notifications'])->toBeFalse();

});        // Test default values

        $this->assertEquals('system', $preferences['theme']);

test('it can cache users list', function () {        $this->assertEquals('id', $preferences['language']);

    User::factory()->count(3)->create();        $this->assertEquals('Asia/Jakarta', $preferences['timezone']);

        $this->assertTrue($preferences['notifications_enabled']);

    $usersList1 = $this->cacheService->getUsersList();    }

    $usersList2 = $this->cacheService->getUsersList();

    #[Test]

    expect($usersList1)->toBeInstanceOf('Illuminate\Database\Eloquent\Collection')    public function it_can_update_user_preferences(): void

        ->and($usersList2)->toBeInstanceOf('Illuminate\Database\Eloquent\Collection')    {

        ->and($usersList1->count())->toBe($usersList2->count())        $user = User::factory()->create();

        ->and($usersList1->count())->toBeGreaterThan(0);

});        $newPreferences = [

            'theme' => 'dark',

test('it handles user list filters', function () {            'language' => 'en',

    $unitKerja1 = UnitKerja::factory()->create(['nama' => 'Unit A']);            'notifications_enabled' => false

    $unitKerja2 = UnitKerja::factory()->create(['nama' => 'Unit B']);        ];



    $user1 = User::factory()->create(['name' => 'User A']);        $this->cacheService->updateUserPreferences($user->id, $newPreferences);

    $user2 = User::factory()->create(['name' => 'User B']);        $cachedPreferences = $this->cacheService->getUserPreferences($user->id);

    $user3 = User::factory()->create(['name' => 'User C']);

        $this->assertEquals('dark', $cachedPreferences['theme']);

    $user1->unitKerjas()->attach($unitKerja1->id);        $this->assertEquals('en', $cachedPreferences['language']);

    $user2->unitKerjas()->attach($unitKerja2->id);        $this->assertFalse($cachedPreferences['notifications_enabled']);

    $user3->unitKerjas()->attach($unitKerja1->id);    }



    $role1 = Role::create(['name' => 'manager']);    #[Test]

    $role2 = Role::create(['name' => 'staff']);    public function it_can_cache_users_list(): void

    {

    $user1->assignRole($role1);        $unitKerja = UnitKerja::factory()->create();

    $user2->assignRole($role2);        $user1 = User::factory()->create();

    $user3->assignRole($role1);        $user1->unitKerjas()->attach($unitKerja->id);

        $user2 = User::factory()->create();

    // Test filter by unit        $user2->unitKerjas()->attach($unitKerja->id);

    $filteredByUnit1 = $this->cacheService->getUsersByUnitKerja($unitKerja1->id);

    $filteredByUnit2 = $this->cacheService->getUsersByUnitKerja($unitKerja2->id);        $usersList1 = $this->cacheService->getUsersList();

        $usersList2 = $this->cacheService->getUsersList();

    expect($filteredByUnit1->count())->toBe(2)

        ->and($filteredByUnit2->count())->toBe(1);        $this->assertEquals($usersList1->count(), $usersList2->count());

        $this->assertEquals(2, $usersList1->count());

    // Test filter by role    }

    $filteredByRole1 = $this->cacheService->getUsersByRole('manager');

    $filteredByRole2 = $this->cacheService->getUsersByRole('staff');    #[Test]

    public function it_handles_user_list_filters(): void

    expect($filteredByRole1->count())->toBe(2)    {

        ->and($filteredByRole2->count())->toBe(1);        $unitKerja1 = UnitKerja::factory()->create();

        $unitKerja2 = UnitKerja::factory()->create();

    // Test combined filter

    $combinedFilter = $this->cacheService->getUsersByRoleAndUnit('manager', $unitKerja1->id);        $role = Role::create(['name' => 'manager']);



    expect($combinedFilter->count())->toBe(2);        $user1 = User::factory()->create([

});            'name' => 'John Doe'

        ]);

test('it can cache role statistics', function () {        $user1->unitKerjas()->attach($unitKerja1->id);

    $role1 = Role::create(['name' => 'admin']);        $user1->assignRole($role);

    $role2 = Role::create(['name' => 'user']);

        $user2 = User::factory()->create([

    $user1 = User::factory()->create();            'name' => 'Jane Smith'

    $user2 = User::factory()->create();        ]);

    $user3 = User::factory()->create();        $user2->unitKerjas()->attach($unitKerja2->id);



    $user1->assignRole($role1);        // Test role filter

    $user2->assignRole($role2);        $roleFiltered = $this->cacheService->getUsersList(['role' => 'manager']);

    $user3->assignRole($role2);        $this->assertEquals(1, $roleFiltered->count());

        $this->assertEquals('John Doe', $roleFiltered->first()->name);

    $stats1 = $this->cacheService->getRoleStatistics();

    $stats2 = $this->cacheService->getRoleStatistics();        // Test unit kerja filter (note: this will need a different filter approach)

        $unitFiltered = $this->cacheService->getUsersList(['unit_kerja_id' => $unitKerja2->id]);

    expect($stats1)->toBeArray()        $this->assertEquals(1, $unitFiltered->count());

        ->and($stats2)->toBeArray()        $this->assertEquals('Jane Smith', $unitFiltered->first()->name);

        ->and($stats1)->toBe($stats2)

        ->and($stats1)->toHaveKeys(['admin', 'user'])        // Test search filter

        ->and($stats1['admin'])->toBe(1)        $searchFiltered = $this->cacheService->getUsersList(['search' => 'John']);

        ->and($stats1['user'])->toBe(2);        $this->assertEquals(1, $searchFiltered->count());

});        $this->assertEquals('John Doe', $searchFiltered->first()->name);

    }

test('it can invalidate user cache', function () {

    $user = User::factory()->create();    #[Test]

    public function it_can_cache_role_statistics(): void

    // Cache some data    {

    $profile1 = $this->cacheService->getUserProfile($user->id);        $adminRole = Role::create(['name' => 'admin']);

    $permissions1 = $this->cacheService->getUserPermissions($user->id);        $userRole = Role::create(['name' => 'user']);



    // Invalidate cache        $permission = Permission::create(['name' => 'manage_users']);

    $result = $this->cacheService->invalidateUserCache($user->id);        $adminRole->givePermissionTo($permission);



    expect($result)->toBeTrue();        $user1 = User::factory()->create();

        $user1->assignRole($adminRole);

    // Verify cache is cleared by checking if new data is fetched

    $profile2 = $this->cacheService->getUserProfile($user->id);        $user2 = User::factory()->create();

            $user2->assignRole($userRole);

    expect($profile2)->not()->toBeNull();

});        $user3 = User::factory()->create(); // No role



test('it can get user activity summary', function () {        $stats = $this->cacheService->getRoleStatistics();

    $user = User::factory()->create();

        $this->assertIsArray($stats);

    $summary1 = $this->cacheService->getUserActivitySummary($user->id);        $this->assertArrayHasKey('total_users', $stats);

    $summary2 = $this->cacheService->getUserActivitySummary($user->id);        $this->assertArrayHasKey('active_users', $stats);

        $this->assertArrayHasKey('role_breakdown', $stats);

    expect($summary1)->toBeArray()        $this->assertArrayHasKey('users_without_roles', $stats);

        ->and($summary2)->toBeArray()

        ->and($summary1)->toBe($summary2)        $this->assertEquals(3, $stats['total_users']);

        ->and($summary1)->toHaveKeys(['last_login', 'total_logins', 'activities_count']);        $this->assertEquals(3, $stats['active_users']);

});        $this->assertEquals(1, $stats['users_without_roles']);

        $this->assertEquals(1, $stats['role_breakdown']['admin']['count']);
        $this->assertEquals(1, $stats['role_breakdown']['user']['count']);
        $this->assertEquals(1, $stats['role_breakdown']['admin']['permissions_count']);
    }

    #[Test]
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

    #[Test]
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
