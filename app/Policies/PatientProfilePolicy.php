<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PatientProfilePolicy
{
    public function view(User $user, PatientProfile $patientProfile): Response
    {
        // Allow if the user is an admin, the owner of the profile, or the doctor related to the patient
        return ($user->role === Role::ADMIN || $user->is($patientProfile->user)
            || ($user->role === Role::DOCTOR && $user->doctorProfile->appointments()->where('patient_id', $user->doctorProfile->id)))
            ? Response::allow() : Response::deny();
    }

    public function update(User $user, PatientProfile $patientProfile): Response
    {
        return $user->role === Role::ADMIN || $user->is($patientProfile->user)
            ? Response::allow() : Response::deny();
    }
}
