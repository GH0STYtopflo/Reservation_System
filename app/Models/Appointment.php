<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }


}
