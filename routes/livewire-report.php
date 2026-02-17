<?php

use App\Http\Controllers\ImutIndicatorReportController;
use App\Http\Controllers\UnitKerjaLaporanController;
use Illuminate\Support\Facades\Route;

// Route untuk report dengan URL yang profesional
Route::prefix('laporan/indikator-mutu')->name('laporan.indikator-mutu.')->group(function () {
    // Basic report page
    // Route::get('/', [ImutIndicatorReportController::class, 'index'])
    //     ->name('index');

    // Report dengan parameter indikator dan periode menggunakan slug
    Route::get('{indicator}/{periode}', [ImutIndicatorReportController::class, 'show'])
        ->name('show')
        ->where([
            'indicator' => '[a-z0-9-]+',
            'periode' => '[0-9]+'
        ]);

    // Report dengan filter periode dan catatan
    Route::get('{indicator}/{periode}/{filter_periode?}/{catatan?}', [ImutIndicatorReportController::class, 'detail'])
        ->name('detail')
        ->where([
            'indicator' => '[a-z0-9-]+',
            'periode' => '[0-9]+',
            'filter_periode' => '[a-z_]+',
            'catatan' => '[0-9]+'
        ]);

    // Unit Kerja Report dengan periode filter
    Route::prefix('unit-kerja')->name('unit-kerja.')->group(function () {
        // Show laporan unit kerja: /laporan/indikator-mutu/unit-kerja/{unitKerja}
        Route::get('{unitKerja}', [UnitKerjaLaporanController::class, 'show'])
            ->name('show');

        // With periode type dan value: /laporan/indikator-mutu/unit-kerja/{unitKerja}/{tipe}/{periode}
        Route::get('{unitKerja}/{tipe}/{periode}', [UnitKerjaLaporanController::class, 'show'])
            ->name('show-with-period')
            ->where([
                'unitKerja' => '[a-z0-9-]+',
                'tipe' => 'yearly|quarterly|semester|custom',
                'periode' => '[a-zA-Z0-9\-,]+'
            ]);
    });
});
