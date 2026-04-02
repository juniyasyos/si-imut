<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/livewire-report.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\RedirectSsoLoginPost::class);
        $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);
        $middleware->append(\Bepsvpt\SecureHeaders\SecureHeadersMiddleware::class);

        // IAM/SSO token verification middleware - check and refresh token on every web request
        $middleware->web(\Juniyasyos\IamClient\Http\Middleware\VerifyIamToken::class);
        $middleware->web(\Juniyasyos\IamClient\Http\Middleware\EnforceSessionTimeout::class);

        // Configure authentication redirects based on IAM/SSO mode
        $middleware->redirectGuestsTo(function () {
            // Check if IAM/SSO is enabled
            if (config('iam.enabled', false) || env('USE_SSO', false)) {
                return route('sso.login');
            }

            // Check if Filament login exists
            if (\Illuminate\Support\Facades\Route::has('filament.siimut.auth.login')) {
                return route('filament.siimut.auth.login');
            }

            // Fallback to sso login path (safe even if route not registered)
            return '/sso/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            // Handle integrity constraint violations (duplicate entries)
            if (
                $e->getCode() === '23000' &&
                (strpos($e->getMessage(), 'unique_periode_laporan') !== false ||
                    strpos($e->getMessage(), 'Duplicate entry') !== false)
            ) {

                // Extract error details
                $isDuplicatePeriod = strpos($e->getMessage(), 'unique_periode_laporan') !== false;

                if ($isDuplicatePeriod && $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Laporan untuk periode tersebut sudah ada',
                        'errors' => [
                            'report_month' => ['Laporan untuk periode ini sudah dibuat sebelumnya'],
                            'report_year' => ['Laporan untuk periode ini sudah dibuat sebelumnya'],
                        ]
                    ], 422);
                }

                if ($isDuplicatePeriod) {
                    // For web requests, let Filament handle with custom error handling we've added
                    // This prevents debug bar from showing
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'report_month' => ['Laporan untuk periode ini sudah dibuat sebelumnya'],
                        'report_year' => ['Laporan untuk periode ini sudah dibuat sebelumnya'],
                    ]);
                }
            }
        });
    })->create();
