<?php

namespace App;

enum AvailabilityDefType: string
{
    case ONCE = 'once';
    case RECURRING = 'recurring';
}
