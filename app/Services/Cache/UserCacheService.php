<?php

namespace App\Services\Cache;

use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Cache Service
 *
 * Handles caching for user-related data including:
 * - User profiles and permissions
 * - User activity and preferences
 * - Unit kerja assignments
 * - Role-based data access
 */
class UserCacheService extends BaseCacheService
{
    protected const USER_PROFILE_TTL = 7200; // 2 hours
    protected const USER_PERMISSIONS_TTL = 3600; // 1 hour
    protected const USER_ACTIVITY_TTL = 1800; // 30 minutes
    protected const USER_PREFERENCES_TTL = 86400; // 24 hours

    protected function getKeyPrefix(): string
    {
        return 'user';
    }

    protected function getCacheTags(): array
    {
        return ['users', 'permissions', 'profiles'];
    }

    /**
     * Cache user profile with relations using tagged approach
     * Following Laravel pattern: Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
     */
    public function getUserProfile(int $userId): ?User
    {
        $key = "profile:{$userId}";

        return $this->remember($key, function () use ($userId) {
            return User::with(['roles', 'permissions', 'unitKerjas'])->find($userId);
        }, self::USER_PROFILE_TTL);
    }

    /**
     * Cache user profile with specific tags for granular invalidation
     * Example: Cache users by role and unit kerja
     */
    public function cacheUserByRoleAndUnit(User $user): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        $userUnits = $user->unitKerjas->pluck('id')->toArray();

        // Create specific tags for this user
        $tags = array_merge(
            ['users'],
            array_map(fn($role) => "role:{$role}", $userRoles),
            array_map(fn($unitId) => "unit:{$unitId}", $userUnits)
        );

        // Following Laravel docs pattern: Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
        return $this->cacheTaggedData($tags, "user:{$user->id}", $user, self::USER_PROFILE_TTL);
    }

    /**
     * Get user by role and unit using tagged cache
     * Following Laravel docs pattern: $john = Cache::tags(['people', 'artists'])->get('John');
     */
    public function getUserByRoleAndUnit(int $userId, string $role, int $unitId): ?User
    {
        $tags = ['users', "role:{$role}", "unit:{$unitId}"];
        return $this->getTaggedData($tags, "user:{$userId}");
    }

    /**
     * Invalidate all users with specific role
     * Following Laravel docs pattern: Cache::tags('authors')->flush();
     */
    public function invalidateUsersByRole(string $role): bool
    {
        return $this->flushByTag("role:{$role}");
    }

    /**
     * Invalidate all users in specific unit
     * Following Laravel docs pattern: Cache::tags(['people', 'authors'])->flush();
     */
    public function invalidateUsersByUnit(int $unitId): bool
    {
        return $this->flushByTag("unit:{$unitId}");
    }

    /**
     * Cache user permissions
     */
    public function getUserPermissions(int $userId): array
    {
        $key = "permissions:{$userId}";

        return $this->remember($key, function () use ($userId) {
            $user = User::with(['roles.permissions', 'permissions'])->find($userId);

            if (!$user) {
                return [];
            }

            // Get permissions from roles
            $rolePermissions = $user->roles->flatMap->permissions->pluck('name');

            // Get direct permissions
            $directPermissions = $user->permissions->pluck('name');

            // Combine and unique
            return $rolePermissions->concat($directPermissions)->unique()->sort()->values()->toArray();
        }, self::USER_PERMISSIONS_TTL);
    }

    /**
     * Cache user's accessible unit kerja
     */
    public function getUserAccessibleUnitKerja(int $userId): Collection
    {
        $key = "accessible_unit_kerja:{$userId}";

        return $this->remember($key, function () use ($userId) {
            $user = User::with(['roles', 'unitKerjas'])->find($userId);

            if (!$user) {
                return collect();
            }

            // If user is admin, return all unit kerja
            if ($user->hasRole('super_admin') || $user->can('view_all_unit_kerja')) {
                return UnitKerja::all();
            }

            // Return user's assigned unit kerja
            return $user->unitKerjas;
        }, self::USER_PROFILE_TTL);
    }

    /**
     * Cache user dashboard data
     */
    public function getUserDashboardData(int $userId): array
    {
        $key = "dashboard:{$userId}";

        return $this->remember($key, function () use ($userId) {
            $user = User::with(['roles', 'unitKerjas'])->find($userId);

            if (!$user) {
                return [];
            }

            // Get user's accessible data based on role and unit kerja
            $accessibleUnitKerja = $this->getUserAccessibleUnitKerja($userId);
            $unitKerjaIds = $accessibleUnitKerja->pluck('id')->toArray();

            // Calculate user-specific statistics
            $stats = [
                'accessible_unit_kerja_count' => count($unitKerjaIds),
                'total_imut_data_count' => 0,
                'pending_assessments' => 0,
                'completed_assessments' => 0,
                'user_role' => $user->roles->pluck('name')->first() ?? 'user',
                'last_activity' => $user->updated_at,
                'recent_activities' => []
            ];

            // Add role-specific data
            if ($user->can('view_imut_data')) {
                // Count IMUT data accessible to user
                $stats['total_imut_data_count'] = \App\Models\ImutData::when(
                    !empty($unitKerjaIds),
                    fn($query) => $query->whereHas('unitKerja', fn($q) => $q->whereIn('unit_kerja.id', $unitKerjaIds))
                )->count();

                $stats['pending_assessments'] = \App\Models\LaporanImut::when(
                    !empty($unitKerjaIds),
                    fn($query) => $query->whereHas('unitKerjas', fn($q) => $q->whereIn('unit_kerja.id', $unitKerjaIds))
                )->where('status', 'pending')->count();

                $stats['completed_assessments'] = \App\Models\LaporanImut::when(
                    !empty($unitKerjaIds),
                    fn($query) => $query->whereHas('unitKerjas', fn($q) => $q->whereIn('unit_kerja.id', $unitKerjaIds))
                )->where('status', 'completed')->count();
            }

            return $stats;
        }, self::USER_ACTIVITY_TTL);
    }

    /**
     * Cache user preferences
     */
    public function getUserPreferences(int $userId): array
    {
        $key = "preferences:{$userId}";

        return $this->remember($key, function () use ($userId) {
            // In a real application, you might have a user_preferences table
            // For now, we'll return default preferences
            return [
                'theme' => 'system',
                'language' => 'id',
                'timezone' => 'Asia/Jakarta',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'dashboard_layout' => 'default',
                'items_per_page' => 15,
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i'
            ];
        }, self::USER_PREFERENCES_TTL);
    }

    /**
     * Cache users list with filters
     */
    public function getUsersList(array $filters = [], int $perPage = 15, int $page = 1): Collection
    {
        $filterKey = md5(serialize($filters));
        $key = "list:{$filterKey}:page_{$page}:per_{$perPage}";

        return $this->remember($key, function () use ($filters, $perPage, $page) {
            $query = User::query();

            // Apply filters
            if (isset($filters['role'])) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            }

            if (isset($filters['unit_kerja_id'])) {
                $query->whereHas('unitKerjas', function ($q) use ($filters) {
                    $q->where('unit_kerja.id', $filters['unit_kerja_id']);
                });
            }

            if (isset($filters['status'])) {
                if ($filters['status'] === 'active') {
                    $query->whereNull('deleted_at');
                } elseif ($filters['status'] === 'inactive') {
                    $query->whereNotNull('deleted_at');
                }
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            }

            return $query->with(['roles', 'unitKerjas'])
                ->orderBy('name')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
        }, self::USER_PROFILE_TTL);
    }

    /**
     * Cache role-based statistics
     */
    public function getRoleStatistics(): array
    {
        $key = 'role_statistics';

        return $this->remember($key, function () {
            $users = User::with('roles')->get();

            $roleStats = [];
            foreach ($users as $user) {
                foreach ($user->roles as $role) {
                    if (!isset($roleStats[$role->name])) {
                        $roleStats[$role->name] = [
                            'count' => 0,
                            'active_count' => 0,
                            'permissions_count' => $role->permissions->count()
                        ];
                    }

                    $roleStats[$role->name]['count']++;
                    if (!$user->deleted_at) {
                        $roleStats[$role->name]['active_count']++;
                    }
                }
            }

            return [
                'total_users' => $users->count(),
                'active_users' => $users->whereNull('deleted_at')->count(),
                'role_breakdown' => $roleStats,
                'users_without_roles' => $users->filter(fn($user) => $user->roles->isEmpty())->count()
            ];
        }, self::USER_ACTIVITY_TTL);
    }

    /**
     * Invalidate cache for specific user
     */
    public function invalidateUser(int $userId): void
    {
        $this->forget("profile:{$userId}");
        $this->forget("permissions:{$userId}");
        $this->forget("accessible_unit_kerja:{$userId}");
        $this->forget("dashboard:{$userId}");
        $this->forget("preferences:{$userId}");

        // Also invalidate list caches and role statistics
        $this->invalidateListCaches();
        $this->forget('role_statistics');
    }

    /**
     * Invalidate all list caches
     */
    public function invalidateListCaches(): void
    {
        // List caches use dynamic keys, so we rely on TTL for those
        // In a production environment, you might want to track these keys
    }

    /**
     * Invalidate role-related caches
     */
    public function invalidateRoleCaches(): void
    {
        $this->forget('role_statistics');

        // When roles change, user permissions might change too
        // We could flush all user permission caches, but that's expensive
        // Better to rely on TTL for permissions
    }

    /**
     * Update user preferences in cache
     */
    public function updateUserPreferences(int $userId, array $preferences): void
    {
        $key = "preferences:{$userId}";
        $this->put($key, $preferences, self::USER_PREFERENCES_TTL);
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId, int $days = 30): array
    {
        $key = "activity_summary:{$userId}:days_{$days}";

        return $this->remember($key, function () use ($userId, $days) {
            // This would typically query an activity log table
            // For now, we'll return a mock structure
            return [
                'user_id' => $userId,
                'period_days' => $days,
                'login_count' => rand(5, 25),
                'last_login' => now()->subDays(rand(0, 7)),
                'pages_visited' => rand(50, 200),
                'actions_performed' => rand(10, 100),
                'most_used_features' => [
                    'dashboard' => rand(20, 50),
                    'laporan_imut' => rand(10, 30),
                    'imut_data' => rand(5, 25),
                    'reports' => rand(5, 20)
                ]
            ];
        }, self::USER_ACTIVITY_TTL);
    }
}
