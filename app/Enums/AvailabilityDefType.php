<?php

namespace App\Enums;

enum AvailabilityDefType: string
{
    case ONCE = 'once';
    case RECURRING = 'recurring';
}
