<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unavailability extends Model
{
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class);
    }
}
