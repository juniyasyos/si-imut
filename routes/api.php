<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GreetingController;
use App\Http\Controllers\Api\ImutBenchmarkingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Greeting Routes
Route::prefix('greeting')->group(function () {
    Route::get('/', [GreetingController::class, 'index']);
    Route::get('/quote/{timeKey?}', [GreetingController::class, 'quote']);
    Route::get('/quotes', [GreetingController::class, 'quotes']);
});

// Benchmark Chart Routes
Route::prefix('chart/imut/{imutDataId}')->group(function () {
    Route::get('/benchmarks', [ImutBenchmarkingController::class, 'getChartData']);
    Route::get('/benchmarks/debug', [ImutBenchmarkingController::class, 'getDebugData']);
});

// Benchmark Management Routes
Route::prefix('benchmarks')->group(function () {
    Route::get('/coverage', [ImutBenchmarkingController::class, 'getCoverage']);
    Route::get('/missing', [ImutBenchmarkingController::class, 'getMissingBenchmarks']);
    Route::post('/bulk-create', [ImutBenchmarkingController::class, 'bulkCreate']);
});

// Route::post('/login', [AuthController::class, 'login']);
