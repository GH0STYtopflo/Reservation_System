<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\SpecialtyController;
use Illuminate\Support\Facades\Route;


// Auth - kinda an anomaly
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::post('auth:login', [AuthController::class, 'login'])->name('login');
    Route::post('auth:register', [AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth:logout', [AuthController::class, 'logout']);
});

// The rest of the routes
Route::middleware('auth:sanctum')->group(function () {
    //--------------------------------------------------------------------------------------
    //-------------------------------------- Profiles --------------------------------------
    //--------------------------------------------------------------------------------------

    Route::middleware('can.any:admin-access,doctor-access')->group(function () {
        Route::post('doctors', [DoctorProfileController::class, 'store']);
        Route::patch('doctors/{doctorProfile}', [DoctorProfileController::class, 'update']);
        Route::put('doctors/{doctorProfile}/specialties', [DoctorProfileController::class, 'updateDoctorSpecialties']);
    });

    Route::get('doctors', [DoctorProfileController::class, 'index']);
    Route::get('doctors/{doctorProfile}', [DoctorProfileController::class, 'show']);
    //--------------------------------------------------------------------------------------


    Route::middleware('can:profile-exists')->group(function () {
        //--------------------------------------------------------------------------------------
        //-------------------------------------- Cities ----------------------------------------
        //--------------------------------------------------------------------------------------
        Route::resource('cities', CityController::class)->only(['index', 'show']);

        Route::resource('cities', CityController::class)->only(['store', 'update', 'destroy'])
            ->middleware('can:admin-access');
        //--------------------------------------------------------------------------------------


        //--------------------------------------------------------------------------------------
        //------------------------------------ Specialties -------------------------------------
        //--------------------------------------------------------------------------------------
        Route::resource('specialties', SpecialtyController::class)->only(['index', 'show']);

        Route::resource('specialties', SpecialtyController::class)->only(['store', 'update', 'destroy'])
            ->middleware('can:admin-access');
        //--------------------------------------------------------------------------------------
    });
});



