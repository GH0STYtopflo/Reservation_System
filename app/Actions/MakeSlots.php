<?php

namespace App\Actions;

use App\Models\Availability;
use Carbon\Carbon;

class MakeSlots implements Action
{

    public function execute(Availability $availability, Carbon $date): array
    {
        $start = Carbon::parse($availability->shift_start_at);
        $end = Carbon::parse($availability->shift_end_at);

        $array = [];
        while ($start->lt($end)) {
            if (SlotAvailable::execute($date->setTimeFromTimeString($start->toTimeString()), $availability->doctor)) {
                $array[] = $start->toTimeString();
            }

            $start = $start->addMinutes(20);
        }

        return $array;
    }
}
