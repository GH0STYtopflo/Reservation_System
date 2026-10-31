<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DoctorProfilePolicy
{
    public function update(User $user, DoctorProfile $doctorProfile): Response
    {
        return
            ($user->role === Role::ADMIN) ||
            ($user->role === Role::DOCTOR && $doctorProfile->user->is($user)) ?
                Response::allow() : Response::deny();
    }
}
