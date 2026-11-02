<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;

class SendCancelNotice
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentCancelled $event): void
    {
        $user = $event->appointment->patient->user;

        $user->notify(new \App\Notifications\AppointmentCancelled($event->appointment));
    }
}
