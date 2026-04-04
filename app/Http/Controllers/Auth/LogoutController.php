<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Juniyasyos\IamClient\Services\UserApplicationsService;
use Juniyasyos\IamClient\Support\IamConfig;

class LogoutController extends Controller
{
    public function __invoke()
    {
        // Check if SSO is enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        if ($ssoEnabled) {
            // Use IAM logout when SSO is enabled
            return $this->handleSSOLogout();
        }

        // Non-SSO logout - redirect to filament panel
        return $this->handleLocalLogout();
    }

    /**
     * Handle SSO logout by redirecting to IAM server
     */
    private function handleSSOLogout()
    {
        $guardName = IamConfig::guardName('web');
        $guardInstance = Auth::guard($guardName);

        $userId = $guardInstance->id();
        $sessionId = session()->getId();

        Log::info('SSO logout initiated', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'guard' => $guardName,
        ]);

        // Clear application cache before logout
        UserApplicationsService::clearUserAppCache($userId);
        UserApplicationsService::clearSessionAppCache();

        $guardInstance->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        request()->session()->forget('iam');

        Log::info('SSO logout completed', [
            'previous_user_id' => $userId,
            'old_session_id' => $sessionId,
            'new_session_id' => session()->getId(),
            'guard' => $guardName,
        ]);

        $iamBase = trim((string) IamConfig::baseUrl());

        if ($iamBase === '') {
            $redirectRouteName = IamConfig::logoutRedirectRoute('web');

            if ($redirectRouteName && Route::has($redirectRouteName)) {
                return redirect()->route($redirectRouteName)->with('message', 'You have been logged out successfully.');
            }

            return redirect(IamConfig::guardRedirect('web'))->with('message', 'You have been logged out successfully.');
        }

        $iamLogoutUrl = rtrim($iamBase, '/') . '/logout';
        return redirect()->away($iamLogoutUrl);
    }

    /**
     * Handle local logout without SSO (redirect using relative path)
     */
    private function handleLocalLogout()
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Log::info('Local logout completed');

        // Use relative path instead of APP_URL to avoid hardcoded localhost
        return redirect(config('filament.path', '/siimut'))->with('message', 'You have been logged out successfully.');
    }
}
