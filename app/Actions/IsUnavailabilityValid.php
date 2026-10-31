<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Unavailability;

class IsUnavailabilityValid implements Action
{
    public static function execute(Unavailability $unavailability, DoctorProfile $doctorProfile): bool
    {
        return ! ($doctorProfile->appointments()->where(
            'start_at',
            '<=',
            $unavailability->end_at
        )
            ->orWhere(
                'end_at',
                '>=',
                $unavailability->start_at
            )
            ->whereNot('status', AppointmentStatus::CANCELLED)
            ->exists());
    }
}
