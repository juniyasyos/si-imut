<?php

namespace App\Http\Middleware;

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
            if (config('iam.enabled', false)) {
                // Redirect to SSO login
                return route('iam.sso.login');
            }

            // Check if Filament login exists
            if (\Illuminate\Support\Facades\Route::has('filament.admin.auth.login')) {
                return route('filament.admin.auth.login');
            }

            // Fallback to login route
            if (\Illuminate\Support\Facades\Route::has('login')) {
                return route('login');
            }

            // Last resort: return login path
            return '/login';
        }

        return null;
    }
}
