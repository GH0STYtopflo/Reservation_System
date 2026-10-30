<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    protected $attributes = [
        'verified_at' => null,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class, 'doctor_id');
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(Unavailability::class, 'doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function doctorSpecialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class,
            'doctor_specialties', 'doctor_id', 'specialty_id');
    }
}
