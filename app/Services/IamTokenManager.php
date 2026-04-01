<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

/**
 * IAM Token Management Service
 * 
 * Handles JWT token refresh, validation, and session storage
 * untuk memastikan access token selalu tersedia saat dibutuhkan
 */
class IamTokenManager
{
    /**
     * Get valid access token, refresh jika diperlukan
     * 
     * @return string|null Access token atau null jika tidak ada
     */
    public function getValidToken(): ?string
    {
        // SSO JWT-only: token harus berasal dari session callback IAM.
        $token = session('iam.access_token');

        if (!empty($token)) {
            Log::debug('[IamToken] Session token available', [
                'has_token' => true,
                'token_length' => strlen($token),
            ]);
            return $token;
        }

        $backupToken = session('iam.access_token_backup');
        if (!empty($backupToken)) {
            Log::info('[IamToken] Using backup session token', [
                'user_id' => Auth::id(),
                'token_length' => strlen($backupToken),
            ]);
            return $backupToken;
        }

        Log::warning('[IamToken] Token tidak ada di session (SSO JWT flow)', [
            'user_id' => Auth::id(),
        ]);

        return null;
    }

    /**
     * Refresh token menggunakan token saat ini (flow Passport /auth/refresh).
     * Endpoint IAM saat ini mengharuskan bearer token aktif.
     */
    public function refreshUsingToken(string $token): ?string
    {
        Log::info('[IamToken] Refresh skipped: JWT SSO-only mode', [
            'user_id' => Auth::id(),
        ]);

        return null;
    }

    /**
     * Refresh JWT token menggunakan refresh_token
     * 
     * @param string $refreshToken
     * @return string|null New access token atau null jika refresh gagal
     */
    public function refreshToken(string $refreshToken): ?string
    {
        Log::info('[IamToken] Refresh token flow disabled in JWT SSO-only mode', [
            'user_id' => Auth::id(),
        ]);

        return null;
    }

    /**
     * Check apakah token ada dan valid di session
     * 
     * @return bool
     */
    public function hasValidToken(): bool
    {
        return !empty(session('iam.access_token'));
    }

    /**
     * Clear tokens dari session (logout)
     * 
     * @return void
     */
    public function clearTokens(): void
    {
        Session::forget(['iam.access_token', 'iam.refresh_token']);
        Log::info('[IamToken] Tokens cleared from session', [
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Debug: Get token info
     * 
     * @return array
     */
    public function getDebugInfo(): array
    {
        $token = session('iam.access_token');
        $refreshToken = session('iam.refresh_token');

        return [
            'has_access_token' => !empty($token),
            'access_token_length' => $token ? strlen($token) : 0,
            'has_refresh_token' => !empty($refreshToken),
            'refresh_token_length' => $refreshToken ? strlen($refreshToken) : 0,
            'session_iam_data' => session('iam', []),
        ];
    }
}
