<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentDueTomorrow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAppointmentNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User $user,
        protected Appointment $appointment
    )
    {
    }

    public function handle(): void
    {
        $this->user->notify(new AppointmentDueTomorrow($this->appointment));
    }
}
