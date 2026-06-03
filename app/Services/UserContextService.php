<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized service for caching user context data (unit_kerja IDs)
 * across all Daily Report services to eliminate redundant queries.
 * 
 * Request-scoped static cache ensures:
 * - Single database query per request
 * - Shared across all services
 * - Automatic cleanup at request end
 * 
 * Usage:
 *   $ids = UserContextService::getUserUnitKerjaIds();
 *   $ids = UserContextService::getUserUnitKerjaIds($userId);
 */
class UserContextService
{
    /**
     * Request-scoped cache for unit_kerja IDs
     * Key: "user_{userId}"
     * 
     * @var array<string, array<int>>
     */
    private static array $unitKerjaCache = [];

    /**
     * Get cached unit_kerja IDs for authenticated user.
     * Queries database only on first call, then caches for entire request.
     * 
     * @return array<int> Unit kerja IDs
     */
    public static function getUserUnitKerjaIds(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        return self::getUserUnitKerjaIdsForUser($user);
    }

    /**
     * Get cached unit_kerja IDs for specific user.
     * Queries database only on first call per user, then caches for entire request.
     * 
     * @param int $userId User ID
     * @return array<int> Unit kerja IDs
     */
    public static function getUserUnitKerjaIdsForUserId(int $userId): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        return self::getUserUnitKerjaIdsForUser($user);
    }

    /**
     * Get cached unit_kerja IDs for specific user model.
     * Queries database only on first call per user, then caches for entire request.
     * 
     * @param User $user
     * @return array<int> Unit kerja IDs
     */
    public static function getUserUnitKerjaIdsForUser(User $user): array
    {
        $cacheKey = "user_{$user->id}";

        // Return cached value if exists
        if (isset(self::$unitKerjaCache[$cacheKey])) {
            return self::$unitKerjaCache[$cacheKey];
        }

        // Query database and cache result
        $unitKerjaIds = $user->unitKerjas()
            ->pluck('unit_kerja.id')
            ->toArray();

        self::$unitKerjaCache[$cacheKey] = $unitKerjaIds;

        return $unitKerjaIds;
    }

    /**
     * Clear cache (useful for testing or manual invalidation)
     */
    public static function clearCache(): void
    {
        self::$unitKerjaCache = [];
    }

    /**
     * Clear cache for specific user (useful for testing)
     */
    public static function clearCacheForUser(int $userId): void
    {
        unset(self::$unitKerjaCache["user_{$userId}"]);
    }
}
