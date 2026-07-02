<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Juniyasyos\IamClient\Services\UserApplicationsService;

class UserApplicationController extends Controller
{
    /**
     * Fetch applications accessible by the current user (for app switcher).
     */
    public function index()
    {
        try {
            if (!auth()->check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated',
                ], 401);
            }

            if (!config('iam.enabled')) {
                return response()->json([
                    'error' => 'IAM Not Enabled',
                    'applications' => [],
                ]);
            }

            $service = app(UserApplicationsService::class);
            $data = $service->getApplications();

            if ($data === null || isset($data['error'])) {
                return response()->json([
                    'error' => 'Failed to fetch applications',
                    'applications' => [],
                ]);
            }

            // Transform response for component
            $applications = collect($data['access_profiles'] ?? [])
                ->flatMap(fn ($profile) => $profile['applications'] ?? [])
                ->filter(fn ($app) => $app['enabled'] ?? true)
                ->map(fn ($app) => [
                    'id' => $app['id'],
                    'name' => $app['name'],
                    'app_key' => $app['app_key'],
                    'app_url' => $app['app_url'],
                    'role' => $app['role']['name'] ?? 'User',
                    'role_slug' => $app['role']['slug'] ?? null,
                    'enabled' => true,
                ])
                ->unique('app_key')
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'applications' => $applications,
                'total' => count($applications),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch user applications', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'error' => 'Server error',
                'applications' => [],
            ], 500);
        }
    }
}
