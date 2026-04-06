<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\IamTokenManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;

/**
 * IAM App Switcher Component
 * 
 * Menampilkan daftar aplikasi dari IAM API dengan caching.
 * Menggunakan lazy loading untuk performa optimal.
 */
#[Lazy]
class IamAppSwitcher extends Component
{
    public $applications = [];
    public $error = null;
    public $loading = false;
    public $open = false;

    private ?IamTokenManager $tokenManager = null;

    // Cache duration (5 minutes)
    private const CACHE_DURATION = 300;

    public function boot()
    {
        $this->tokenManager = app(IamTokenManager::class);
    }

    public function mount()
    {
        if (config('iam.enabled') && session()->has('iam.sub')) {
            $this->loadApplications();
        }
    }

    /**
     * Generate cache key based on user
     */
    private function getCacheKey(): string
    {
        return 'iam.apps.user.' . Auth::id();
    }

    /**
     * Load aplikasi dengan cache.
     */
    public function loadApplications()
    {
        try {
            $this->loading = true;
            $this->error = null;

            // Check user authentication
            if (!session()->has('iam.sub')) {
                $this->applications = [];
                return;
            }

            $cacheKey = $this->getCacheKey();

            // Try to get from cache first
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                $this->applications = $cached;
                $this->loading = false;
                return;
            }

            // If not in cache, fetch from IAM API
            $token = $this->tokenManager->getValidToken();

            if (empty($token)) {
                $this->applications = [];
                $this->error = 'Token IAM tidak tersedia. Silakan login ulang.';
                Log::warning('IamAppSwitcher: Missing IAM token, cannot call API', [
                    'user_id' => Auth::id(),
                ]);
                return;
            }

            Log::info('IamAppSwitcher: Calling IAM applications detail API', [
                'user_id' => Auth::id(),
            ]);

            $data = $this->fetchApplicationsFromIam($token);

            if (!is_array($data) || !isset($data['applications']) || !is_array($data['applications'])) {
                $this->applications = [];
                $this->error = 'Gagal mengambil data aplikasi dari IAM server.';
                return;
            }

            $applications = $this->transformApplications($data['applications']);

            // Cache the result
            Cache::put($cacheKey, $applications, self::CACHE_DURATION);

            $this->applications = $applications;
        } catch (\Exception $e) {
            Log::error('IamAppSwitcher: Exception during load', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);

            $this->applications = [];
            $this->error = 'Terjadi kesalahan saat menghubungi IAM server.';
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Fetch data aplikasi detail dari IAM API.
     */
    private function fetchApplicationsFromIam(string $token): ?array
    {
        $baseUrl = rtrim((string) config('iam.base_url'), '/');
        $url = $baseUrl . '/api/users/applications/detail';

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(10)
            ->get($url);

        if (!$response->successful()) {
            Log::warning('IamAppSwitcher: IAM applications API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'user_id' => Auth::id(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Transform API aplikasi data ke format yang siap tampil
     */
    private function transformApplications(array $apps): array
    {
        return collect($apps)
            ->filter(fn($app) => ($app['status'] ?? 'active') === 'active')
            ->map(fn($app) => [
                'id' => $app['id'] ?? null,
                'app_key' => $app['app_key'] ?? null,
                'name' => $app['name'] ?? 'Unknown Application',
                'description' => $app['description'] ?? null,
                'enabled' => ($app['status'] ?? 'active') === 'active',
                'logo_url' => $app['metadata']['logo']['url'] ?? ($app['logo_url'] ?? null),
                'has_logo' => $app['metadata']['logo']['available'] ?? !empty($app['logo_url']),
                'app_url' => $app['metadata']['urls']['primary']
                    ?? $app['app_url']
                    ?? null,
                'redirect_uris' => $app['metadata']['urls']['all_redirects']
                    ?? $app['redirect_uris']
                    ?? [],
                'status' => $app['status'] ?? 'active',
                'roles_count' => $app['roles_count'] ?? count($app['roles'] ?? []),
                'roles' => collect($app['roles'] ?? [])
                    ->map(fn($role) => [
                        'id' => $role['id'] ?? null,
                        'slug' => $role['slug'] ?? null,
                        'name' => $role['name'] ?? 'User',
                        'description' => $role['description'] ?? null,
                        'is_system' => $role['is_system'] ?? false,
                    ])
                    ->toArray(),
                'urls' => $app['metadata']['urls'] ?? $app['urls'] ?? [
                    'primary' => $app['app_url'] ?? null,
                    'all_redirects' => $app['redirect_uris'] ?? [],
                ],
            ])
            ->filter(fn($app) => !empty($app['app_url']))
            ->values()
            ->toArray();
    }

    public function navigateTo($appUrl)
    {
        if (!$appUrl) {
            return;
        }

        Log::info('User navigating to app', [
            'app_url' => $appUrl,
            'user_id' => Auth::id()
        ]);

        return redirect($appUrl);
    }

    public function toggleOpen()
    {
        $this->open = !$this->open;
    }

    /**
     * Force refresh cache
     */
    public function refreshCache()
    {
        Cache::forget($this->getCacheKey());
        $this->loadApplications();
    }

    public function render()
    {
        return view('livewire.iam-app-switcher');
    }
}
