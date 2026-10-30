<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UnavailabilityController;
use App\Models\Availability;
use App\Models\MedicalHistory;
use Illuminate\Support\Facades\Route;

Route::get('test', function () {
    return Availability::get()->toResourceCollection();
});

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
    // --------------------------------------------------------------------------------------
    // ----------------------------------- Doctor Profiles ----------------------------------
    // --------------------------------------------------------------------------------------

    Route::middleware('can.any:admin-access,doctor-access')->group(function () {
        Route::post('doctors', [DoctorProfileController::class, 'store'])->name('doctors.store');
        Route::patch('doctors/{doctorProfile}', [DoctorProfileController::class, 'update']);
        Route::put('doctors/{doctorProfile}/specialties', [DoctorProfileController::class, 'updateDoctorSpecialties']);
    });

    Route::get('doctors', [DoctorProfileController::class, 'index']);
    Route::get('doctors/{doctorProfile}', [DoctorProfileController::class, 'show']);
    // --------------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------------
    // ----------------------------------- Patient Profiles ---------------------------------
    // --------------------------------------------------------------------------------------

    Route::get('patients', [PatientProfileController::class, 'index'])->middleware('can:admin-access');

    Route::post('patients', [PatientProfileController::class, 'store'])
        ->middleware('can.any:admin-access,patient-access')
        ->name('patients.store');
    ;

    Route::get('patients/{patientProfile}', [PatientProfileController::class, 'show']);

    Route::patch('patients/{patientProfile}', [PatientProfileController::class, 'update']);
    // --------------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------------
    // ----------------------------------- MedicalHistory -----------------------------------
    // --------------------------------------------------------------------------------------
    Route::get('patients/{patientProfile}/medical_histories', [MedicalHistoryController::class, 'index']);

    Route::get('patients/{patientProfile}/medical_histories/{medicalHistory}', [MedicalHistoryController::class, 'show']);

    Route::post('patients/{patientProfile}/medical_histories', [MedicalHistoryController::class, 'store']);

    Route::patch('patients/{patientProfile}/medical_histories/{medicalHistory}', [MedicalHistoryController::class, 'update']);
    // --------------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------------
    // ----------------------------------- Availabilities -----------------------------------
    // --------------------------------------------------------------------------------------

    Route::post('doctors/{doctorProfile}/availabilities', [AvailabilityController::class, 'store']);

    Route::delete('doctors/{doctorProfile}/availabilities/{availability}', [AvailabilityController::class, 'destroy']);

    Route::get('doctors/{doctorProfile}/availabilities/{availability}', [AvailabilityController::class, 'show']);

    Route::patch('doctors/{doctorProfile}/availabilities/{availability}', [AvailabilityController::class, 'update']);

    Route::get('doctors/{doctorProfile}/available-slots', [AvailabilityController::class, 'indexAvailableSlots']);

    Route::get('doctors/{doctorProfile}/availabilities', [AvailabilityController::class, 'index']);

    // --------------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------------
    // ----------------------------------- Unavailabilities ---------------------------------
    // --------------------------------------------------------------------------------------

    Route::post('doctors/{doctorProfile}/unavailabilities', [UnavailabilityController::class, 'store']);

    // --------------------------------------------------------------------------------------

    Route::middleware('can:profile-exists')->group(function () {
        // --------------------------------------------------------------------------------------
        // -------------------------------------- Cities ----------------------------------------
        // --------------------------------------------------------------------------------------
        Route::resource('cities', CityController::class)->only(['index', 'show']);

        Route::resource('cities', CityController::class)->only(['store', 'update', 'destroy'])
            ->middleware('can:admin-access');
        // --------------------------------------------------------------------------------------

        // --------------------------------------------------------------------------------------
        // ------------------------------------ Specialties -------------------------------------
        // --------------------------------------------------------------------------------------
        Route::resource('specialties', SpecialtyController::class)->only(['index', 'show']);

        Route::resource('specialties', SpecialtyController::class)->only(['store', 'update', 'destroy'])
            ->middleware('can:admin-access');
        // --------------------------------------------------------------------------------------
    });
});
