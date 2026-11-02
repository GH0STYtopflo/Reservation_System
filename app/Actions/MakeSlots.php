<?php

namespace App\Actions;

use App\Models\Availability;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class MakeSlots implements Action
{
    public static function execute(Availability $availability, CarbonImmutable $date): array
    {
        $start = Carbon::parse($availability->shift_start_at);
        $end = Carbon::parse($availability->shift_end_at);

        $array = [];
        while ($start->lt($end)) {
            if (SlotAvailable::execute($date->setTimeFromTimeString($start->toTimeString()), $availability->doctor)) {
                $array[] = $start->toTimeString();
            }

            $start = $start->addMinutes(30);
        }

        return $array;
    }
}
