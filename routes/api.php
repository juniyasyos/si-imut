<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GreetingController;
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

// Route::post('/login', [AuthController::class, 'login']);
