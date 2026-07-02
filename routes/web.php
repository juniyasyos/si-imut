<?php

use App\Http\Controllers\Api\UserApplicationController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FallbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrintReportController;
use App\Http\Controllers\TableViewController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Middleware\RedirectIfSsoDisabled;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Juniyasyos\IamClient\Http\Controllers\SsoCallbackController;
use Juniyasyos\IamClient\Http\Controllers\SsoLoginRedirectController;

// Include Livewire report routes
require_once __DIR__ . '/livewire-report.php';

Route::middleware(['web'])->group(function () {
    // Root route redirect
    Route::get('/', HomeController::class)->name('home');

    // Unified login entrypoint (flexible between SSO and Filament login)
    // - In SSO mode, redirects to SSO (IAM) login
    // - In local/dev mode, redirects to Filament's login page
    Route::get('/login', SsoLoginRedirectController::class)->name('login');

    // SSO Routes - with middleware to redirect to Filament login when SSO is disabled
    Route::middleware([RedirectIfSsoDisabled::class])->group(function () {
        Route::get('/sso/login', SsoLoginRedirectController::class)->name('sso.login');
        Route::get('/sso/callback', SsoCallbackController::class)->name('sso.callback');
        Route::view('/sso/status', 'auth-status')->name('sso.status');
    });

    Route::post('/logout', LogoutController::class)->name('logout');

    // Table View Route
    Route::get('/table-view', [TableViewController::class, 'index'])
        ->middleware(['auth'])
        ->name('table-view');

    Route::get('/api/table-data', [TableViewController::class, 'getData'])
        ->middleware(['auth'])
        ->name('api.table-data');

    // Export Monitoring Route
    Route::get('/export/monitoring/{templateId}', [ExportController::class, 'monitoringExport'])
        ->middleware(['auth'])
        ->name('export.monitoring');

    // Debug routes - available in all modes
    Route::get('/debug-session', [DebugController::class, 'session'])
        ->name('debug.session');

    // API endpoint untuk app switcher - fetch aplikasi yang bisa diakses user
    Route::get('/api/user-applications', [UserApplicationController::class, 'index'])
        ->middleware(['auth'])
        ->name('api.user-applications');

    // Fallback for legacy login URLs when SSO is enabled.
    // This prevents 404 when users hit `/siimut/login` or `/admin/login` in production.
    Route::fallback(FallbackController::class);
});
