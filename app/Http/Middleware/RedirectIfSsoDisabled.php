<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSsoDisabled
{
    /**
     * Handle an incoming request.
     *
     * Middleware ini akan redirect ke /admin/login ketika SSO disabled (development mode)
     * dan user mencoba mengakses SSO routes seperti /login atau /oauth/callback
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if SSO is enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);
        
        // If SSO is disabled (development mode) and user is trying to access SSO routes
        if (!$ssoEnabled) {
            // Redirect to Filament custom login page
            return redirect('/admin/login');
        }
        
        // SSO enabled, continue with normal flow
        return $next($request);
    }
}
