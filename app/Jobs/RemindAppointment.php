<?php

namespace App\Jobs;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentDueTomorrow;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemindAppointment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User        $user,
        protected int $appointmentId,
    )
    {
    }

    public function handle(): void
    {
        $appointment = Appointment::find($this->appointmentId);

        if (!$appointment) {
            return;
        }

        $this->user->notify(new AppointmentDueTomorrow($appointment));
    }
}
