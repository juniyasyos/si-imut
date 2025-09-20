<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as ResponseInterface;

/**
 * Enhanced Rate Limiting Middleware
 *
 * Provides advanced rate limiting with different strategies
 * for different types of requests and users
 */
class EnhancedRateLimitingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'default'): ResponseInterface
    {
        $response = $this->handleRateLimit($request, $limiter);

        if ($response !== null) {
            return $response;
        }

        return $next($request);
    }

    /**
     * Handle rate limiting logic
     */
    protected function handleRateLimit(Request $request, string $limiter): ?ResponseInterface
    {
        $key = $this->resolveRequestSignature($request, $limiter);
        $config = $this->getLimiterConfig($limiter);

        // Check if request should be rate limited
        if (!$this->shouldRateLimit($request, $limiter)) {
            return null;
        }

        // Apply rate limiting
        $executed = RateLimiter::attempt(
            $key,
            $config['max_attempts'],
            function () {
                // Allow the request
            },
            $config['decay_seconds']
        );

        if (!$executed) {
            return $this->buildFailureResponse($request, $key, $config);
        }

        // Log suspicious activity
        $this->logSuspiciousActivity($request, $key);

        return null;
    }

    /**
     * Resolve the rate limiting key
     */
    protected function resolveRequestSignature(Request $request, string $limiter): string
    {
        $config = $this->getLimiterConfig($limiter);

        $identifier = match ($config['identifier']) {
            'ip' => $request->ip(),
            'user' => $request->user()?->id ?? $request->ip(),
            'session' => $request->session()->getId() ?? $request->ip(),
            'api_key' => $request->header('X-API-Key') ?? $request->ip(),
            default => $request->ip(),
        };

        return sprintf('%s:%s:%s', $limiter, $identifier, $request->route()?->getName() ?? 'route');
    }

    /**
     * Get limiter configuration
     */
    protected function getLimiterConfig(string $limiter): array
    {
        $configs = [
            'default' => [
                'max_attempts' => 60,
                'decay_seconds' => 60,
                'identifier' => 'ip',
                'bypass_roles' => ['super-admin'],
            ],
            'auth' => [
                'max_attempts' => 5,
                'decay_seconds' => 900, // 15 minutes
                'identifier' => 'ip',
                'bypass_roles' => [],
            ],
            'api' => [
                'max_attempts' => 100,
                'decay_seconds' => 60,
                'identifier' => 'api_key',
                'bypass_roles' => ['api-admin'],
            ],
            'upload' => [
                'max_attempts' => 10,
                'decay_seconds' => 300, // 5 minutes
                'identifier' => 'user',
                'bypass_roles' => ['super-admin'],
            ],
            'strict' => [
                'max_attempts' => 10,
                'decay_seconds' => 60,
                'identifier' => 'ip',
                'bypass_roles' => ['super-admin'],
            ],
            'admin' => [
                'max_attempts' => 120,
                'decay_seconds' => 60,
                'identifier' => 'user',
                'bypass_roles' => ['super-admin'],
            ],
        ];

        return $configs[$limiter] ?? $configs['default'];
    }

    /**
     * Determine if request should be rate limited
     */
    protected function shouldRateLimit(Request $request, string $limiter): bool
    {
        $config = $this->getLimiterConfig($limiter);

        // Check if user has bypass roles
        if ($request->user() && !empty($config['bypass_roles'])) {
            $userRoles = $request->user()->roles->pluck('name')->toArray();

            if (!empty(array_intersect($userRoles, $config['bypass_roles']))) {
                return false;
            }
        }

        // Check if IP is whitelisted
        if ($this->isWhitelistedIp($request->ip())) {
            return false;
        }

        // Check if request is from internal services
        if ($this->isInternalRequest($request)) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is whitelisted
     */
    protected function isWhitelistedIp(string $ip): bool
    {
        $whitelistedIps = config('security.rate_limiting.whitelist_ips', [
            '127.0.0.1',
            '::1',
        ]);

        return in_array($ip, $whitelistedIps);
    }

    /**
     * Check if request is from internal services
     */
    protected function isInternalRequest(Request $request): bool
    {
        // Check for internal service headers
        return $request->header('X-Internal-Service') === config('app.internal_service_key');
    }

    /**
     * Build failure response
     */
    protected function buildFailureResponse(Request $request, string $key, array $config): ResponseInterface
    {
        $headers = $this->getRetryHeaders($key, $config);

        // Log rate limit exceeded
        $this->logRateLimitExceeded($request, $key, $config);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $headers['Retry-After'] ?? $config['decay_seconds'],
            ], 429, $headers);
        }

        return response()->view('errors.429', [
            'retry_after' => $headers['Retry-After'] ?? $config['decay_seconds'],
        ], 429, $headers);
    }

    /**
     * Get retry headers
     */
    protected function getRetryHeaders(string $key, array $config): array
    {
        $retryAfter = RateLimiter::availableIn($key);
        $remainingAttempts = RateLimiter::remaining($key, $config['max_attempts']);

        return [
            'X-RateLimit-Limit' => $config['max_attempts'],
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
            'Retry-After' => $retryAfter,
        ];
    }

    /**
     * Log rate limit exceeded events
     */
    protected function logRateLimitExceeded(Request $request, string $key, array $config): void
    {
        logger()->warning('Rate limit exceeded', [
            'key' => $key,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'route' => $request->route()?->getName(),
            'user_agent' => $request->userAgent(),
            'config' => $config,
        ]);

        // Increment suspicious activity counter
        $suspiciousKey = "suspicious_activity:" . $request->ip();
        Cache::increment($suspiciousKey, 1);
        Cache::expire($suspiciousKey, 3600); // 1 hour
    }

    /**
     * Log suspicious activity patterns
     */
    protected function logSuspiciousActivity(Request $request, string $key): void
    {
        $attempts = RateLimiter::attempts($key);
        $config = $this->getLimiterConfig('default');

        // Log if approaching rate limit
        if ($attempts > ($config['max_attempts'] * 0.8)) {
            logger()->info('High rate limit usage detected', [
                'key' => $key,
                'attempts' => $attempts,
                'max_attempts' => $config['max_attempts'],
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'route' => $request->route()?->getName(),
            ]);
        }

        // Check for potential attack patterns
        $this->detectAttackPatterns($request);
    }

    /**
     * Detect potential attack patterns
     */
    protected function detectAttackPatterns(Request $request): void
    {
        $ip = $request->ip();
        $suspiciousKey = "suspicious_activity:" . $ip;
        $suspiciousCount = Cache::get($suspiciousKey, 0);

        // If too many suspicious activities from same IP
        if ($suspiciousCount > 10) {
            logger()->warning('Potential attack detected', [
                'ip' => $ip,
                'suspicious_count' => $suspiciousCount,
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
            ]);

            // Auto-block IP for short period
            $blockKey = "blocked_ip:" . $ip;
            Cache::put($blockKey, true, 300); // 5 minutes
        }
    }
}
