<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FallbackController extends Controller
{
    /**
     * Handle fallback routes — redirect legacy login URLs when SSO is enabled.
     */
    public function __invoke(Request $request)
    {
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);
        $path = trim($request->path(), '/');

        if (in_array($path, ['siimut/login', 'admin/login'], true) && $ssoEnabled) {
            return redirect('/login');
        }

        if ($path === 'login' && !$ssoEnabled) {
            return redirect(\Filament\Facades\Filament::getLoginUrl());
        }

        abort(404);
    }
}
