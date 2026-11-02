<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\MedicalHistory;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicalHistoryPolicy
{
    public function vc(User $user, PatientProfile $patientProfile): Response
    {
        return (
            $user->role === Role::ADMIN ||
            $user->is($patientProfile->user) ||
            ($user->role === Role::DOCTOR && $user->doctorProfile->appointments()
                ->where('patient_id', $patientProfile->id)
                ->exists())
        ) ? Response::allow() : Response::deny();
    }

    public function vud(User $user, MedicalHistory $medicalHistory): Response
    {
        return (
            $user->role === Role::ADMIN ||
            $user->is($medicalHistory->patientProfile->user) ||
            ($user->role === Role::DOCTOR && $user->doctorProfile->appointments()
                ->where('patient_id', $medicalHistory->patientProfile->id)
                ->exists())
        ) ? Response::allow() : Response::deny();
    }
}
