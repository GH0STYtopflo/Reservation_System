<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Enums\AvailabilityDefType;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\DoctorProfile;

class RuleHasAppointments implements Action
{
    public static function execute(Availability $availability, DoctorProfile $doctorProfile): bool
    {
        if (
            $availability->type === AvailabilityDefType::ONCE
        ) {
            $start = $availability->date->setTimeFromTimeString($availability->shift_start_at->toTimeString());
            $end = $availability->date->setTimeFromTimeString($availability->shift_end_at->toTimeString());


            if (
                $doctorProfile->appointments()
                    ->whereNot('end_at', '<', $start)
                    ->whereNot('start_at', '>', $end)
            ) {
                return true;
            }
        }

        // At this point we know that the availability type is recurring.
        return $doctorProfile->appointments->contains(
            fn (Appointment $appointment) =>
                $availability->rrule->occursAt(
                    $appointment->start_at->toDateString()
                )
                && RulesIntersect::hoursIntersect(
                    [
                        $availability->shift_start_at->toTimeString(),
                        $availability->shift_end_at->toTimeString(),
                    ],
                    [
                        $appointment->start_at->toTimeString(),
                        $appointment->end_at->toTimeString(),
                    ]
                )
                && $appointment->status !== AppointmentStatus::CANCELLED
        );
    }
}
