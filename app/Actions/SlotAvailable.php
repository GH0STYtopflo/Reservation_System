<?php

namespace App\Actions;

use App\Actions\Action;
use App\Enums\AppointmentStatus;
use App\Models\DoctorProfile;
use Carbon\Carbon;

class SlotAvailable implements Action
{
    public static function execute(Carbon $timestamp, DoctorProfile $doctorProfile): bool
    {
        return !(($doctorProfile->unavailabilities()
                ->whereValueBetween($timestamp->toAtomString(), ['start_at', 'end_at'])
                ->exists()

            ||

            $doctorProfile->appointments()
                ->where('date', $timestamp->toDateString())
                ->whereValueBetween($timestamp->toTimeString(), ['start_at', 'end_at'])
                ->whereNot('status', AppointmentStatus::CANCELLED)
                ->exists()));
    }
}
