<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if IAM/SSO is enabled
            if (config('iam.enabled', false) || env('USE_SSO', false)) {
                // Redirect to SSO login
                return route('sso.login');
            }

            // Check if Filament login exists
            if (Route::has('filament.siimut.auth.login')) {
                return route('filament.siimut.auth.login');
            }

            // Fallback to sso login path (safe even if route not registered)
            return '/sso/login';
        }

        return null;
    }
}
