<?php

namespace App\Actions;

use App\Enums\AvailabilityDefType;
use App\Enums\DayOfWeek;
use App\Models\Availability;
use App\Models\DoctorProfile;
use Carbon\Carbon;

class RulesIntersect implements Action
{
    public static function execute(DoctorProfile $doctorProfile, Availability $new): bool
    {
        foreach ($doctorProfile->availabilities as $av) {
            $avRecurring = $av->type === AvailabilityDefType::RECURRING;
            $newRecurring = $new->type === AvailabilityDefType::RECURRING;

            if (! ($avRecurring || $newRecurring)) {
                if (Carbon::parse($av->date)->format('Y-m-d') !== Carbon::parse($new->date)->format('Y-m-d')) {
                    continue;
                }
            } else {
                $recurring = $avRecurring ? $av : $new;
                $other = $avRecurring ? $new : $av;

                $days = explode(',', explode(';', $recurring->rrule->rfcString())[1]);
                $days[0] = substr($days[0], 6);

                $day = $other->type === AvailabilityDefType::RECURRING
                    ? explode(',', explode(';', $other->rrule->rfcString())[1])
                    : [DayOfWeek::from(Carbon::parse($other->date)->dayOfWeek)->name];

                if ($other->type === AvailabilityDefType::RECURRING) {
                    $day[0] = substr($day[0], 6);
                }

                if (! self::daysIntersect($days, $day)) {
                    continue;
                }
            }

            if (self::hoursIntersect(
                [$av->shift_start_at, $av->shift_end_at],
                [$new->shift_start_at, $new->shift_end_at],
            )) {
                return true;
            }
        }

        return false;
    }

    public static function daysIntersect(array $d, array $dNew): bool
    {
        return ! empty(array_intersect($dNew, $d));
    }

    public static function hoursIntersect(array $h, array $hNew): bool
    {
        return ! ($hNew[1] < $h[0] || $hNew[0] > $h[1]);
    }
}
