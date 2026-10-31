<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Availability;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AvailabilityPolicy
{
    public function vc(User $user, DoctorProfile $doctorProfile): Response
    {
        return
            ($user->role === Role::ADMIN) ||
            ($user->role === Role::DOCTOR && $doctorProfile->user->is($user)) ?
                Response::allow() : Response::deny();
    }


    public function vud(User $user, Availability $availability): Response
    {
        return
            ($availability->doctor->user->is($user)) ||
            ($user->role === Role::ADMIN) ?
                Response::allow() : Response::deny();
    }
}
