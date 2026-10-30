<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DoctorProfilePolicy
{
    public function mutate(User $user, DoctorProfile $doctorProfile): Response
    {
        return ($user->role == Role::DOCTOR && $user->is($doctorProfile->user)) || $user->role == Role::ADMIN ?
            Response::allow() : Response::deny('You are not mutate this resource.');
    }
}
