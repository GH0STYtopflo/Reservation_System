<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    public function doctorSpecialties(): BelongsToMany
    {
        return $this->belongsToMany(DoctorProfile::class,
            'doctor_specialties', 'specialty_id', 'doctor_id');
    }
}
