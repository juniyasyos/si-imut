<?php

namespace App\Filament\Responses\Auth;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector as IlluminateRedirector;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportRedirects\Redirector;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Return an appropriate HTTP response after the user has been logged out by
     * the Filament `LogoutController`.
     *
     * When the application is configured in SSO mode we do not send the user back
     * to the panel login page; instead we hand them off to the IAM client so the
     * external identity provider can be notified and any additional cleanup can
     * occur (`/iam/logout` route).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
     */
    public function toResponse($request): RedirectResponse | Redirector
    {
        // check both config and .env because the repo historically uses either
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        if ($ssoEnabled) {
            // the route name is defined by the IAM client package
            Log::info('SSO logout: redirecting to IAM logout route');
            if (\Route::has('iam.iam.logout')) {
                return redirect()->route('iam.iam.logout');
            }

            // fallback to literal path if the named route isn't available yet
            return redirect('/iam/logout');
        }

        // When not in SSO mode just send the user back to the panel.  We avoid
        // invoking Filament helpers here because third‑party plugins may call
        // `auth()->user()` and blow up when there is no active user (which is
        // what happened during automated tests). A hardcoded path is sufficient
        // for our purposes.
        return redirect(config('filament.path', '/siimut'));

    }
}
