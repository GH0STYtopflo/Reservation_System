<?php

namespace App\Http\Controllers;

use App\Actions\AppointmentTimeValid;
use App\Enums\AppointmentStatus;
use App\Enums\Role;
use App\Http\Requests\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Appointment\RescheduleAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Jobs\RemindAppointment;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use Auth;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Gate;
use http\Exception\RuntimeException;
use Illuminate\Support\Facades\DB;
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

        $datetime = CarbonImmutable::parse($data['datetime']);

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

        $job = new RemindAppointment($patient->user, $app);

        if ($datetime->gt(CarbonImmutable::now()->addHours(24))) {
            dispatch($job)->delay($datetime->subDay());
        } else {
            dispatch($job);
        }

        return $app->toResource();
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return $appointment->unsetRelation('doctor')->toResource();
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

        $appointment->patient_note = $request->validated()['patient_notes'] ?? $appointment->patient_note;

        $appointment->save();

        return $appointment->unsetRelation('patient')->toResource();
    }

    public function rescheduleAppointment(RescheduleAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('reschedule-cancel', $appointment);

        $datetime = CarbonImmutable::parse($request->validated()['datetime']);

        $user = Auth::user();

        if (
            $appointment->status === AppointmentStatus::CANCELLED ||
            $appointment->status === AppointmentStatus::COMPLETED ||
            CarbonImmutable::parse($appointment->end_at)->lt(now())
        ) {
            return Response::json([
                "message" => "The appointment cannot be rescheduled.",
            ], 409);
        }


        try {
            $appointment = DB::transaction(function () use ($request, $appointment, $datetime, $user) {
                $appointment->delete();

                if (!AppointmentTimeValid::execute($datetime, $appointment->doctor)) {
                    throw new RuntimeException();
                }

                $appointment->start_at = $datetime;
                $appointment->end_at = $datetime->addMinutes(30);

                if ($user->role === Role::PATIENT) {
                    $appointment->status = AppointmentStatus::PENDING;
                }

                $appointment->save();

                return $appointment->refresh();
            });
        } catch (\Throwable) {
            return Response::json([
                "message" => "The appointment time is not valid.",
            ],
                409);
        }

        $job = new RemindAppointment($appointment->patient->user, $appointment);

        if ($datetime->gt(CarbonImmutable::now()->addHours(24))) {
            dispatch($job)->delay($datetime->subDay());
        } else {
            dispatch($job);
        }

        return $appointment->unsetRelation('patient')->unsetRelation('doctor')->toResource();
    }

    public function cancelAppointment(CancelAppointmentRequest $request, Appointment $appointment)
    {
        Gate::authorize('reschedule-cancel', $appointment);

        if (CarbonImmutable::parse($appointment->start_at)->lte(now()->addMinutes(15))) {
            return Response::json([
                "message" => "The appointment cannot be cancelled.",
            ],
                409);
        }

        if ($appointment->status === AppointmentStatus::COMPLETED) {
            return Response::json([
                "message" => "A completed appointment cannot be cancelled.",
            ], 409);
        }

        if ($appointment->status === AppointmentStatus::CANCELLED) {
            return Response::json([
                "message" => "Appointment already cancelled.",
            ], 409);
        }

        $appointment->status = AppointmentStatus::CANCELLED;
        $appointment->cancellation_reason = $request->cancellation_reason;
        $appointment->cancelled_at = CarbonImmutable::now();

        $appointment->save();

        return $appointment->unsetRelation('patient')->toResource();
    }

    public function approveAppointment(Appointment $appointment)
    {
        Gate::authorize('approve-complete', $appointment);

        if ($appointment->status === AppointmentStatus::CANCELLED ||
            CarbonImmutable::parse($appointment->start_at)->lt(now())) {
            return Response::json([
                "message" => "The appointment cannot be approved.",
            ], 409);
        }

        $appointment->status = AppointmentStatus::APPROVED;

        $appointment->save();

        return $appointment->toResource();
    }

    public function completeAppointment(Appointment $appointment)
    {
        Gate::authorize('approve-complete', $appointment);

        if ($appointment->status === AppointmentStatus::CANCELLED ||
            CarbonImmutable::parse($appointment->start_at)->lt(now())) {
            return Response::json([
                "message" => "The appointment cannot be completed.",
            ], 409);
        }

        $appointment->status = AppointmentStatus::COMPLETED;

        $appointment->save();

        return $appointment->toResource();
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();


        return \response()->noContent();
    }
}
