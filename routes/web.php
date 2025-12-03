<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PrintReportController;
use Illuminate\Support\Facades\Auth;
use Juniyasyos\IamClient\Http\Controllers\LogoutController;
use Juniyasyos\IamClient\Http\Controllers\SsoCallbackController;
use Juniyasyos\IamClient\Http\Controllers\SsoLoginRedirectController;

// Print Report Routes
Route::prefix('print')->name('print.')->group(function () {
    // Preview dengan dummy data
    Route::get('/preview/imut-data-report', [PrintReportController::class, 'previewImutDataReport'])
        ->name('preview.imut-data-report');

    Route::get('/preview/imut-indicator-report', [PrintReportController::class, 'previewImutIndicatorReport'])
        ->name('preview.imut-indicator-report')
        ->middleware(['auth', 'can:view_all_data_imut::data']);

    // Print real data (dengan laporan_id)
    Route::get('/imut-data-report', [PrintReportController::class, 'printImutDataReport'])
        ->name('imut-data-report');

    Route::get('/imut-indicator-report', [PrintReportController::class, 'printImutIndicatorReport'])
        ->name('imut-indicator-report');
});

Route::middleware(['web'])->group(function () {
    // SSO Routes - dengan middleware redirect untuk development mode
    // Ketika SSO disabled (USE_SSO=false), routes ini akan redirect ke /admin/login
    Route::middleware([\App\Http\Middleware\RedirectIfSsoDisabled::class])->group(function () {
        Route::get('/login', SsoLoginRedirectController::class)->name('login');
        Route::get('/oauth/callback', SsoCallbackController::class)->name('sso.callback');
        Route::view('/status', 'auth-status')->name('status');
    });

    Route::post('/logout', LogoutController::class)->name('logout');

    // Debug routes - available in all modes
    Route::get('/debug-session', function () {
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
    })->name('debug.session');
});
