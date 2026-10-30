<?php

namespace App\Models;

use App\Enums\HistoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalHistory extends Model
{
    protected $attributes = [
        'type' => HistoryType::CONDITION,
    ];

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }
}
