<?php

namespace App\Listeners;

use App\Events\AppointmentApproved;

class SendApproveNotice
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
    public function handle(AppointmentApproved $event): void
    {
        $user = $event->appointment->patient->user;

        $user->notify(new \App\Notifications\AppointmentApproved($event->appointment));
    }
}
