<?php

namespace App\Policies;

use App\Models\MedicalHistory;
use App\Models\PatientProfile;
use App\Models\User;
use App\Role;
use Illuminate\Auth\Access\Response;

class MedicalHistoryPolicy
{
    public function crud(User $user, PatientProfile $patientProfile): Response
    {
        return (
            $user->role === Role::ADMIN ||
            $user->is($patientProfile->user) ||
            ($user->role === Role::DOCTOR && $user->doctorProfile->appointments()->where('patient_id', $patientProfile->id)->exists())
        ) ? Response::allow() : Response::deny('You do not have permission to view any medical history from this profile.');
    }
}
