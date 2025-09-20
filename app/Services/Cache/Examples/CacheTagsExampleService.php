<?php

namespace App\Services\Cache\Examples;

use App\Services\Cache\BaseCacheService;
use App\Models\LaporanImut;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Cache Tags Usage Examples
 *
 * This class demonstrates how to properly use Laravel Cache Tags
 * following the official documentation patterns.
 */
class CacheTagsExampleService extends BaseCacheService
{
    protected function getKeyPrefix(): string
    {
        return 'example';
    }

    protected function getCacheTags(): array
    {
        return ['examples'];
    }

    /**
     * Example 1: Basic Tagged Caching (From Laravel Docs)
     *
     * Laravel Pattern:
     * Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
     * Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
     */
    public function exampleBasicTaggedCaching()
    {
        // Store John with tags 'people' and 'artists'
        $john = (object) ['name' => 'John', 'profession' => 'artist'];
        Cache::tags(['people', 'artists'])->put('John', $john, 3600);

        // Store Anne with tags 'people' and 'authors'
        $anne = (object) ['name' => 'Anne', 'profession' => 'author'];
        Cache::tags(['people', 'authors'])->put('Anne', $anne, 3600);

        // Retrieve tagged data
        $retrievedJohn = Cache::tags(['people', 'artists'])->get('John');
        $retrievedAnne = Cache::tags(['people', 'authors'])->get('Anne');

        return [$retrievedJohn, $retrievedAnne];
    }

    /**
     * Example 2: Selective Cache Invalidation (From Laravel Docs)
     */
    public function exampleSelectiveInvalidation()
    {
        // Setup data
        $this->exampleBasicTaggedCaching();

        // Laravel Pattern: Cache::tags(['people', 'authors'])->flush();
        // This removes all caches tagged with either 'people', 'authors', or both
        // So both Anne and John would be removed
        Cache::tags(['people', 'authors'])->flush();

        // Check if data is gone
        $johnAfterFlush = Cache::tags(['people', 'artists'])->get('John'); // null
        $anneAfterFlush = Cache::tags(['people', 'authors'])->get('Anne'); // null

        return [$johnAfterFlush, $anneAfterFlush]; // [null, null]
    }

    /**
     * Example 3: Single Tag Invalidation (From Laravel Docs)
     */
    public function exampleSingleTagInvalidation()
    {
        // Setup data
        $this->exampleBasicTaggedCaching();

        // Laravel Pattern: Cache::tags('authors')->flush();
        // This removes only caches tagged with 'authors'
        // So Anne would be removed, but not John
        Cache::tags('authors')->flush();

        // Check results
        $johnAfterFlush = Cache::tags(['people', 'artists'])->get('John'); // Still there
        $anneAfterFlush = Cache::tags(['people', 'authors'])->get('Anne'); // null

        return [$johnAfterFlush, $anneAfterFlush]; // [John object, null]
    }

    /**
     * Example 4: Real-world IMUT Application - User Management
     */
    public function exampleUserManagement()
    {
        $adminUser = (object) ['id' => 1, 'name' => 'Admin', 'role' => 'admin', 'unit_id' => 1];
        $staffUser = (object) ['id' => 2, 'name' => 'Staff', 'role' => 'staff', 'unit_id' => 1];
        $managerUser = (object) ['id' => 3, 'name' => 'Manager', 'role' => 'manager', 'unit_id' => 2];

        // Cache users with role and unit tags
        Cache::tags(['users', 'role:admin', 'unit:1'])->put('user:1', $adminUser, 3600);
        Cache::tags(['users', 'role:staff', 'unit:1'])->put('user:2', $staffUser, 3600);
        Cache::tags(['users', 'role:manager', 'unit:2'])->put('user:3', $managerUser, 3600);

        // Selective invalidation examples:

        // 1. Clear all users in unit 1
        Cache::tags('unit:1')->flush(); // Removes admin and staff, keeps manager

        // 2. Clear all admin users across all units
        Cache::tags('role:admin')->flush(); // Removes only admin users

        // 3. Clear all users (any role, any unit)
        Cache::tags('users')->flush(); // Removes all users

        return 'User management cache examples completed';
    }

    /**
     * Example 5: Real-world IMUT Application - Laporan Management
     */
    public function exampleLaporanManagement()
    {
        $laporanQ1 = (object) ['id' => 1, 'name' => 'Q1 2025', 'year' => 2025, 'quarter' => 'Q1', 'status' => 'complete'];
        $laporanQ2 = (object) ['id' => 2, 'name' => 'Q2 2025', 'year' => 2025, 'quarter' => 'Q2', 'status' => 'process'];
        $laporan2024Q4 = (object) ['id' => 3, 'name' => 'Q4 2024', 'year' => 2024, 'quarter' => 'Q4', 'status' => 'complete'];

        // Cache laporan with year, quarter, and status tags
        Cache::tags(['laporan', 'year:2025', 'quarter:Q1', 'status:complete'])->put('laporan:1', $laporanQ1, 7200);
        Cache::tags(['laporan', 'year:2025', 'quarter:Q2', 'status:process'])->put('laporan:2', $laporanQ2, 7200);
        Cache::tags(['laporan', 'year:2024', 'quarter:Q4', 'status:complete'])->put('laporan:3', $laporan2024Q4, 7200);

        // Selective invalidation examples:

        // 1. Clear all 2025 data
        Cache::tags('year:2025')->flush(); // Removes Q1 and Q2 2025, keeps 2024 Q4

        // 2. Clear all completed laporan
        Cache::tags('status:complete')->flush(); // Removes Q1 2025 and Q4 2024, keeps Q2 2025

        // 3. Clear all Q1 data across all years
        Cache::tags('quarter:Q1')->flush(); // Removes only Q1 data

        // 4. Clear all laporan
        Cache::tags('laporan')->flush(); // Removes all laporan data

        return 'Laporan management cache examples completed';
    }

    /**
     * Example 6: Hierarchical Cache Invalidation
     */
    public function exampleHierarchicalInvalidation()
    {
        // Organization -> Department -> Team -> User hierarchy
        $orgData = (object) ['id' => 1, 'name' => 'IMUT Organization'];
        $deptData = (object) ['id' => 10, 'name' => 'IT Department', 'org_id' => 1];
        $teamData = (object) ['id' => 100, 'name' => 'Development Team', 'dept_id' => 10];
        $userData = (object) ['id' => 1000, 'name' => 'John Developer', 'team_id' => 100];

        // Cache with hierarchical tags (general -> specific)
        Cache::tags(['org:1'])->put('org:1', $orgData, 3600);
        Cache::tags(['org:1', 'dept:10'])->put('dept:10', $deptData, 3600);
        Cache::tags(['org:1', 'dept:10', 'team:100'])->put('team:100', $teamData, 3600);
        Cache::tags(['org:1', 'dept:10', 'team:100', 'user:1000'])->put('user:1000', $userData, 3600);

        // Cascade invalidation:

        // 1. Clear entire organization (affects all levels)
        Cache::tags('org:1')->flush(); // Removes org, dept, team, and user data

        // 2. Clear department (affects dept, team, user but not org)
        // Cache::tags('dept:10')->flush();

        // 3. Clear team (affects team and user but not dept/org)
        // Cache::tags('team:100')->flush();

        // 4. Clear user (affects only user)
        // Cache::tags('user:1000')->flush();

        return 'Hierarchical invalidation examples completed';
    }

    /**
     * Example 7: Using Our BaseCacheService Methods
     */
    public function exampleUsingBaseCacheServiceMethods()
    {
        $userData = (object) ['id' => 1, 'name' => 'Test User'];

        // Using our enhanced methods

        // 1. Cache with specific tags
        $this->cacheTaggedData(['users', 'test', 'example'], 'test_user', $userData, 1800);

        // 2. Retrieve with specific tags
        $retrievedUser = $this->getTaggedData(['users', 'test', 'example'], 'test_user');

        // 3. Selective invalidation
        $this->flushByTag('test'); // Removes only test-tagged data
        $this->flushByTags(['users', 'example']); // Removes data tagged with either users or example

        return $retrievedUser;
    }

    /**
     * Example 8: Performance Comparison
     */
    public function examplePerformanceComparison()
    {
        // Before tags: Manual key management and broad invalidation
        $oldWay = function() {
            // Had to manually track all keys
            Cache::put('user:1:profile', $userData = (object)['id' => 1], 3600);
            Cache::put('user:1:permissions', $permissions = ['read', 'write'], 3600);
            Cache::put('user:1:preferences', $preferences = ['theme' => 'dark'], 3600);
            Cache::put('user:2:profile', $userData2 = (object)['id' => 2], 3600);
            Cache::put('user:2:permissions', $permissions2 = ['read'], 3600);

            // To clear user 1 data, need to know all keys
            Cache::forget('user:1:profile');
            Cache::forget('user:1:permissions');
            Cache::forget('user:1:preferences');
            // Easy to miss keys or clear too much
        };

        // With tags: Elegant and efficient
        $newWay = function() {
            // Cache with tags
            Cache::tags(['users', 'user:1'])->put('profile', $userData = (object)['id' => 1], 3600);
            Cache::tags(['users', 'user:1'])->put('permissions', $permissions = ['read', 'write'], 3600);
            Cache::tags(['users', 'user:1'])->put('preferences', $preferences = ['theme' => 'dark'], 3600);
            Cache::tags(['users', 'user:2'])->put('profile', $userData2 = (object)['id' => 2], 3600);
            Cache::tags(['users', 'user:2'])->put('permissions', $permissions2 = ['read'], 3600);

            // To clear user 1 data: single operation
            Cache::tags('user:1')->flush(); // All user:1 data gone, user:2 untouched
        };

        return 'Performance comparison examples completed';
    }
}
