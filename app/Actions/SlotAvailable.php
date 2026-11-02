<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Models\DoctorProfile;
use Carbon\CarbonImmutable;

class SlotAvailable implements Action
{
    public static function execute(CarbonImmutable $timestamp, DoctorProfile $doctorProfile): bool
    {
        return !$doctorProfile->unavailabilities()
            ->whereValueBetween($timestamp->toAtomString(), ['start_at', 'end_at'])
            ->exists() && !$doctorProfile->appointments()
            ->whereValueBetween($timestamp, ['start_at', 'end_at'])
            ->whereNot('status', AppointmentStatus::CANCELLED)
            ->exists();
    }
}
