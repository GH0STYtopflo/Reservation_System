<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Greeting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GreetNewUsers implements ShouldQueue
{
    use Queueable;
    public function __construct(protected User $user)
    {
    }

    public function handle(): void
    {
        $this->user->notify(new Greeting());
    }
}
