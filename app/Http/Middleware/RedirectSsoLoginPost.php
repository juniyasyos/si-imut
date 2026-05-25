<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectSsoLoginPost
{
    /**
     * Handle an incoming request.
     *
     * Redirect requests to /siimut/login to SSO login when SSO is enabled and custom
     * login page is not registered. This prevents 405 Method Not Allowed errors in
     * production with SSO mode.
     *
     * @param Request $request
     * @param Closure(Request):Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if SSO is enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        // Check if custom login page is registered
        $hasCustomLoginPage = Route::has('filament.siimut.auth.login');

        // If SSO is enabled and custom login page is NOT registered,
        // redirect any /siimut/login access to SSO
        // if ($ssoEnabled && !$hasCustomLoginPage && $request->path() === 'siimut/login') {
        //     return redirect()->route('sso.login');
        // }

        return $next($request);
    }
}
