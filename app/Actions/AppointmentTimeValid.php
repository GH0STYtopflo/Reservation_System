<?php

namespace App\Actions;

use App\Enums\AvailabilityDefType;
use App\Models\DoctorProfile;
use Carbon\CarbonImmutable;

class AppointmentTimeValid implements Action
{
    public static function execute(CarbonImmutable $datetime, DoctorProfile $doctor): bool
    {
        $availabilities = $doctor->availabilities()
            ->where('type', AvailabilityDefType::ONCE)
            ->where('date', $datetime)
            ->get();

        $availabilities = $availabilities->concat(
            $doctor->availabilities()
                ->where('type', AvailabilityDefType::RECURRING)
                ->get()
                ->filter(function ($availability) use ($datetime) {
                    return $availability->rrule->occursAt($datetime->toDateString());
                })
        );

        if ($availabilities->isEmpty()) {
            return false;
        }

        foreach ($availabilities as $availability) {
            $slots = MakeSlots::execute($availability, $datetime->setTime(0, 0));

            if (! empty(array_intersect($slots, [$datetime->toTimeString()]))) {
                return true;
            }
        }

        return false;
    }
}
