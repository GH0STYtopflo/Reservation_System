<?php

namespace App\Http\Controllers;

use App\Actions\AppointmentTimeValid;
use App\Enums\AppointmentStatus;
use App\Enums\Role;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class AppointmentController extends Controller
{
    public function index()
    {
        $user = \Auth::user();

        if ($user->role === Role::PATIENT) {
            return $user->patientProfile->appointments->toResourceCollection();
        } elseif ($user->role === Role::DOCTOR) {
            return $user->doctorProfile->appointments->toResourceCollection();
        } else {
            return Appointment::get()->toResourceCollection();
        }
    }

    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validated();

        $datetime = Carbon::parse($data['datetime']);

        $doctor = DoctorProfile::find($data['doctor_id']);

        $patient = \Auth::user()->patientProfile;

        if (!AppointmentTimeValid::execute($datetime, $doctor)) {
            return Response::json([
                "message" => "The appointment time is not valid.",
            ],
                409);
        }

        $app = $patient->appointments()->create([
            'doctor_id' => $data['doctor_id'],
            'start_at' => $datetime,
            'end_at' => $datetime->addMinutes(30),
            'status' => AppointmentStatus::PENDING,
            'patient_note' => $data['patient_notes'],
        ]);

        return $app->toResource();
    }

    public function show(Appointment $appointment)
    {
        return $appointment->toResource();
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->patient_note = $request->patient_note;

        $appointment->save();

        return $appointment->toResource();
    }

    public static function cancelAppointment(CancelAppointmentRequest $request, Appointment $appointment)
    {
        if ($appointment->start_at->lte(now()->addMinutes(15))) {
            return Response::json([
                "message" => "The appointment cannot be cancelled.",
            ],
                409);
        }

        $appointment->status = AppointmentStatus::CANCELLED;
        $appointment->cancellation_reason = $request->cancellation_reason;

        $appointment->save();

        return $appointment->toResource();
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
    }
}
