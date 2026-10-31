<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Unavailability;

class IsUnavailabilityValid implements Action
{
    public static function execute(Unavailability $unavailability): bool
    {
        return ! (Appointment::where(
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
