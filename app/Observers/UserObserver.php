<?php

namespace App\Observers;

use App\Jobs\GreetNewUsers;
use App\Models\User;
use App\Notifications\Greeting;

class UserObserver
{
    public function created(User $user): void
    {
        $user->notify(new Greeting());
    }
}
