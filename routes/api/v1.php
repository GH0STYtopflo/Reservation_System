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
    dd(DB::select('SHOW TIME ZONE'));
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

Route::get('doctors', [DoctorProfileController::class, 'index']);
Route::get('doctors/{doctorProfile}', [DoctorProfileController::class, 'show']);

Route::get('doctors/{doctorProfile}/available-slots', [
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


    Route::get('patients/{patientProfile}', [PatientProfileController::class, 'show'])
        ->middleware([
            'can.any:patient-access,admin-access,doctor-access',
            'can:profile-exists',
        ]);

    Route::patch('patients/{patientProfile}', [PatientProfileController::class, 'update'])
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
    | Medical History
    |--------------------------------------------------------------------------
    */

    Route::get('patients/{patientProfile}/medical_history', [MedicalHistoryController::class, 'index']);

    Route::get('patients/{patientProfile}/medical_history/{medicalHistory}', [MedicalHistoryController::class, 'show']);

    Route::post('patients/{patientProfile}/medical_history', [MedicalHistoryController::class, 'store']);

    Route::patch('patients/{patientProfile}/medical_history/{medicalHistory}', [MedicalHistoryController::class, 'update']);


    /*
    |--------------------------------------------------------------------------
    | Doctors
    |--------------------------------------------------------------------------
    */

    Route::post('doctors', [DoctorProfileController::class, 'store'])
        ->middleware('can.any:admin-access,doctor-access');

    Route::patch('doctors/{doctorProfile}', [DoctorProfileController::class, 'update'])
        ->middleware([
            'can.any:admin-access,doctor-access',
            'can:profile-exists',
        ])
        ->whereNumber('doctorProfile'); // This is so the verification route and this route
                                                  //  don't interfere with each other

    Route::put('doctors/{doctorProfile}/specialties', [DoctorProfileController::class, 'updateDoctorSpecialties'])
        ->middleware(['can.any:admin-access,doctor-access', 'can:profile-exists']);

    Route::patch('doctors/{doctorProfile}:verify', [DoctorProfileController::class, 'verifyDoctor'])
        ->middleware('can:admin-access');

    Route::middleware([
        'can.any:admin-access,doctor-access',
        'can:profile-exists',
    ])->group(function () {
        Route::resource('doctors/{doctorProfile}/availabilities', AvailabilityController::class)
            ->except(['create', 'edit', 'update']);

        Route::put('doctors/{doctorProfile}/availabilies', [AvailabilityController::class, 'update']);

        Route::resource('doctors/{doctorProfile}/unavailabilities', UnavailabilityController::class)
            ->except(['create', 'edit', 'update']);

        Route::put('doctors/{doctorProfile}/unavailabilities/{unavailability}', [UnavailabilityController::class, 'update']);
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
            ->middleware('can:patient-access')
            ->whereNumber('appointment');

        Route::patch('appointments/{appointment}:cancel', [AppointmentController::class, 'cancelAppointment'])
            ->middleware('can.any:patient-access,doctor-access')
            ->whereNumber('appointment');

        Route::patch('appointments/{appointment}:approve', [AppointmentController::class, 'approveAppointment'])
            ->middleware('can:doctor-access')
            ->whereNumber('appointment');

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
