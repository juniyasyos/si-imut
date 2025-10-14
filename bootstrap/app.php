<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\EnsureUserIsActive::class);
        $middleware->append(\Bepsvpt\SecureHeaders\SecureHeadersMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            // Handle integrity constraint violations (duplicate entries)
            if ($e->getCode() === '23000' && 
                (strpos($e->getMessage(), 'unique_periode_laporan') !== false ||
                 strpos($e->getMessage(), 'Duplicate entry') !== false)) {
                
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
