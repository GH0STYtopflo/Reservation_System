<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UnavailabilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::post('auth:login', [AuthController::class, 'login'])->name('login');
    Route::post('auth:register', [AuthController::class, 'register']);
});

Route::post('auth:logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::resource('doctors', DoctorProfileController::class)
    ->only(['index', 'show']);

Route::get('doctors/{doctor}/availability', [
    AvailabilityController::class,
    'indexAvailableSlots',
]);

Route::resource('cities', CityController::class)
    ->only(['index', 'show']);

Route::resource('specialties', SpecialtyController::class)
    ->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Patients
    |--------------------------------------------------------------------------
    */

    Route::get('patients', [PatientProfileController::class, 'index'])
        ->middleware('can:admin-access');

    Route::post('patients', [PatientProfileController::class, 'store'])
        ->middleware('can.any:admin-access,patient-access');

    Route::middleware([
        'can.any:patient-access,admin-access',
        'can:profile-exists',
    ])->group(function () {
        Route::get('patients/{patient}', [PatientProfileController::class, 'show']);
        Route::put('patients/{patient}', [PatientProfileController::class, 'update']);
    });

    Route::resource(
        'patients/{patientProfile}/medical-history',
        MedicalHistoryController::class
    )
        ->except(['create', 'edit'])
        ->middleware('can:profile-exists');


    /*
    |--------------------------------------------------------------------------
    | Doctors
    |--------------------------------------------------------------------------
    */

    Route::post('doctors/{doctor}', [DoctorProfileController::class, 'store'])
        ->middleware('can.any:admin-access,doctor-access');

    Route::patch('doctors/{doctor}', [DoctorProfileController::class, 'update'])
        ->middleware([
            'can:admin-access,doctor-access',
            'can:profile-exists',
        ]);

    Route::middleware([
        'can:admin-access,doctor-access',
        'can:profile-exists',
    ])->group(function () {
        Route::resource('doctors/{doctor}/availability', AvailabilityController::class)
            ->except(['create', 'edit']);

        Route::resource('doctors/{doctor}/unavailabilities', UnavailabilityController::class)
            ->except(['create', 'edit']);
    });


    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    Route::middleware('can:admin-access')->group(function () {
        Route::resource('cities', CityController::class)
            ->only(['store', 'update', 'destroy']);

        Route::resource('specialties', SpecialtyController::class)
            ->only(['store', 'update', 'destroy']);
    });
});
