<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PrintReportController;

// Print Report Routes
Route::prefix('print')->name('print.')->group(function () {
    // Preview dengan dummy data
    Route::get('/preview/imut-data-report', [PrintReportController::class, 'previewImutDataReport'])
        ->name('preview.imut-data-report');

    Route::get('/preview/imut-indicator-report', [PrintReportController::class, 'previewImutIndicatorReport'])
        ->name('preview.imut-indicator-report');

    // Print real data (dengan laporan_id)
    Route::get('/imut-data-report', [PrintReportController::class, 'printImutDataReport'])
        ->name('imut-data-report');

    Route::get('/imut-indicator-report', [PrintReportController::class, 'printImutIndicatorReport'])
        ->name('imut-indicator-report');
});
