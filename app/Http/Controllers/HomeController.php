<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __invoke()
    {
        if (auth()->check()) {
            return redirect('/siimut');
        }

        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        if ($ssoEnabled) {
            return redirect()->route('sso.login');
        }

        return redirect('/siimut/login');
    }
}
