<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UnavailabilityController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test
|--------------------------------------------------------------------------
*/

Route::get('test', function () {
    //    DB::transaction(function () {
    //        User::find(1)->delete();
    //
    //        dd(User::find(1));
    //    });
});

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

Route::get('doctors/{doctor}/available-slots', [
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


    Route::get('patients/{patient}', [PatientProfileController::class, 'show'])
        ->middleware([
            'can.any:patient-access,admin-access,doctor-access',
            'can:profile-exists',
        ]);

    Route::put('patients/{patient}', [PatientProfileController::class, 'update'])
        ->middleware([
            'can.any:patient-access,admin-access',
            'can:profile-exists',
            ]);

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

    Route::put('doctors/{doctor}/specialties', [DoctorProfileController::class, 'updateDoctorSpecialties'])
        ->middleware(['can.any:admin-access,doctor-access', 'can:profile-exists']);

    Route::patch('doctors/{doctor}:verify', [DoctorProfileController::class, 'verifyDoctor'])
        ->middleware('can:admin-access');

    Route::middleware([
        'can:admin-access,doctor-access',
        'can:profile-exists',
    ])->group(function () {
        Route::resource('doctors/{doctor}/availability', AvailabilityController::class)
            ->except(['create', 'edit', 'update']);

        Route::put('doctors/{doctor}/availability', [AvailabilityController::class, 'update']);

        Route::resource('doctors/{doctor}/unavailabilities', UnavailabilityController::class)
            ->except(['create', 'edit', 'update']);

        Route::put('doctors/{doctor}/unavailabilities', [UnavailabilityController::class, 'update']);
    });


    /*
    |--------------------------------------------------------------------------
    | Appointments
    |--------------------------------------------------------------------------
    */

    Route::middleware(['can:profile-exists', 'can:doctor-profile-verified'])->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index']);

        Route::post('appointments', [AppointmentController::class, 'store'])
            ->middleware('can:patient-access');

        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);

        Route::patch('appointments/{appointment}', [AppointmentController::class, 'update'])
            ->middleware('can:patient-access');

        Route::patch('appointments/{appointment}:cancel', [AppointmentController::class, 'cancelAppointment'])
            ->middleware('can.any:patient-access,doctor-access');

        Route::patch('appointments/{appointment}:approve', [AppointmentController::class, 'approveAppointment'])
            ->middleware('can:doctor-access');

        // This reschedules the appointment
        Route::put('appointments/{appointment}', [AppointmentController::class, 'rescheduleAppointment'])
            ->middleware('can.any:patient-access,doctor-access');

        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])
            ->middleware('can:admin-access');
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
