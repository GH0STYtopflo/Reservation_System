<?php

namespace App\Models;

use App\Casts\RRuleCast;
use App\Casts\TimeCast;
use App\Enums\AvailabilityDefType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id');
    }

    protected function casts(): array
    {
        return [
            'rrule' => RRuleCast::class,
            'type' => AvailabilityDefType::class,
        ];
    }
}
