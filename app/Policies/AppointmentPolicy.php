<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->role === Role::DOCTOR) {
            return $appointment->doctor->user->is($user);
        } elseif ($user->role === Role::PATIENT) {
            return $appointment->patient->user->is($user);
        } elseif ($user->role === Role::ADMIN) {
            return true;
        }

        return false;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->role === Role::PATIENT && $appointment->patient->user->is($user);
    }

    public function rescheduleCancel(User $user, Appointment $appointment): bool
    {
        if ($user->role === Role::DOCTOR) {
            return $appointment->doctor->user->is($user);
        } elseif ($user->role === Role::PATIENT) {
            return $appointment->patient->user->is($user);
        }

        return false;
    }

    public function approve(User $user, Appointment $appointment): bool
    {
        return $user->role === Role::DOCTOR && $appointment->doctor->user->is($user);
    }
}
