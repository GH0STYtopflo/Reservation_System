<?php

namespace App\Listeners;

use App\Events\AppointmentRescheduled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRescheduleNotice
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentRescheduled $event): void
    {
        $user = $event->appointment->patient->user;

        $user->notify(new AppointmentRescheduled($event->appointment));
    }
}
