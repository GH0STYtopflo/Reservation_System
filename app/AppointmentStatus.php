<?php

namespace App;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
}
