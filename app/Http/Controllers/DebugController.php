<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    /**
     * Display session debug information.
     */
    public function session()
    {
        return response()->json([
            'sso_enabled' => config('iam.enabled', false) || env('USE_SSO', false),
            'app_env' => config('app.env'),
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
            'auth_user' => Auth::user(),
            'session_data' => session()->all(),
            'cookies' => request()->cookies->all(),
            'laravel_session_cookie' => request()->cookie('laravel_session'),
        ]);
    }
}
