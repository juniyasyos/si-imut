<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IamApplicationService
{
    /**
     * Fetch accessible applications directly from IAM server
     */
    public function getAccessibleApplications(): ?array
    {
        try {
            if (!config('iam.enabled')) {
                Log::debug('IAM not enabled');
                return null;
            }

            $user = auth()->user();
            if (!$user) {
                Log::debug('No authenticated user');
                return null;
            }

            // Get IAM base URL
            $iamBaseUrl = config('iam.base_url');
            if (!$iamBaseUrl) {
                Log::error('IAM base URL not configured');
                return null;
            }

            $endpoint = rtrim($iamBaseUrl, '/') . '/iam/user-applications';

            // Get access token from session
            $token = session('iam.access_token') ?? session('iam.access_token_backup');

            Log::info('Fetching IAM applications', [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
                'has_token' => !empty($token),
            ]);

            // Build request with Bearer token if available
            $request = Http::timeout(10);
            if ($token) {
                $request = $request->withToken($token);
            }

            $response = $request->get($endpoint);

            if (!$response->successful()) {
                Log::warning('Failed to fetch applications from IAM server', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $data = $response->json();

            Log::info('Successfully fetched applications from IAM server', [
                'count' => count($data['applications'] ?? []),
                'timestamp' => $data['timestamp'] ?? null,
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Error fetching IAM applications', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get formatted applications list for display
     */
    public function getFormattedApplications(): array
    {
        $data = $this->getAccessibleApplications();

        if (!$data || empty($data['applications'])) {
            return [];
        }

        return collect($data['applications'])
            ->map(fn($app) => [
                'id' => $app['id'] ?? null,
                'app_key' => $app['app_key'] ?? null,
                'name' => $app['name'] ?? null,
                'app_url' => $app['app_url'] ?? $app['urls']['primary'] ?? null,
                'logo_url' => $app['logo_url'] ?? null,
                'enabled' => (bool) ($app['enabled'] ?? true),
            ])
            ->filter(fn($app) => $app['enabled'] && $app['app_url'])
            ->values()
            ->toArray();
    }
}
