<?php

namespace App\Listeners;

use App\Events\AppointmentRescheduled;
use App\Events\AppointmentReserved;
use App\Jobs\RemindAppointment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class QueueReminderNotification
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
    public function handle(AppointmentRescheduled|AppointmentReserved $event): void
    {
        $appointment = $event->appointment;

        $datetime = CarbonImmutable::parse($appointment->start_at);

        $job = new RemindAppointment($appointment->patient->user, $appointment->id);

        if ($datetime->gt(CarbonImmutable::now()->addHours(24))) {
            dispatch($job)->delay($datetime->subDay());
        } else {
            dispatch($job);
        }
    }
}
