<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\DoctorProfile;
use App\Models\Unavailability;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UnavailabilityPolicy
{
    public function vc(User $user, DoctorProfile $doctorProfile): Response
    {
        return
            ($user->role === Role::ADMIN) ||
            ($user->role === Role::DOCTOR && $doctorProfile->user->is($user)) ?
                Response::allow() : Response::deny();
    }

    public function vud(User $user, Unavailability $unavailability): Response
    {
        return
            $unavailability->doctor->user->is($user) ||
            ($user->role === Role::ADMIN) ?
                Response::allow() : Response::deny();
    }
}
