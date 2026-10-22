<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Auth - kinda an anomaly
Route::prefix('auth')->group(function () {
    Route::middleware(['guest', 'throttle:6,1'])->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// The rest of the routes
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('cities', CityController::class)->except(['edit', 'create']);
});



