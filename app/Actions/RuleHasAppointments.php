<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\AvailabilityDefType;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\DoctorProfile;
use Carbon\Carbon;

class RuleHasAppointments implements Action
{
    public static function execute(Availability $availability, DoctorProfile $doctorProfile): bool
    {
        if (
            $availability->type === AvailabilityDefType::ONCE
        ) {
            $date = Carbon::parse($availability->date);

            $start = $date->setTimeFromTimeString(Carbon::parse($availability->shift_start_at)->toTimeString());
            $end = $date->setTimeFromTimeString(Carbon::parse($availability->shift_end_at)->toTimeString());


            if (
                $doctorProfile->appointments()
                    ->whereNot('end_at', '<', $start)
                    ->whereNot('start_at', '>', $end)
                ->exists()
            ) {
                return true;
            }
        }

        // At this point we know that the availability type is recurring.
        return $doctorProfile->appointments->contains(
            fn (Appointment $appointment) =>
                $availability->rrule->occursAt(
                    Carbon::parse($appointment->start_at)->toDateString()
                )
                && RulesIntersect::hoursIntersect(
                    [
                        Carbon::parse($availability->shift_start_at)->toTimeString(),
                        Carbon::parse($availability->shift_end_at)->toTimeString(),
                    ],
                    [
                        Carbon::parse($appointment->start_at)->toTimeString(),
                        Carbon::parse($appointment->end_at)->toTimeString(),
                    ]
                )
                && $appointment->status !== AppointmentStatus::CANCELLED
        );
    }
}
