<?php

namespace App\Policies;

use App\Models\City;
use App\Models\User;
use App\Role;
use Illuminate\Auth\Access\Response;

class CityPolicy
{
    public function delete(User $user, City $city): Response
    {
        return $user->role === Role::ADMIN ? Response::allow() : Response::denyAsNotFound();
    }
}
