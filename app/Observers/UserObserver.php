<?php

namespace App\Observers;

use App\Jobs\GreetNewUsers;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        GreetNewUsers::dispatch($user);
    }
}
