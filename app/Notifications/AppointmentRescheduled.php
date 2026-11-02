<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Appointment $appointment)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $datetime = CarbonImmutable::parse($this->appointment->start_at);

        return (new MailMessage)
            ->subject('Appointment Rescheduled')
            ->greeting('Hello!')
            ->line('Your appointment has been successfully rescheduled.')
            ->line('**Doctor:** ' . $this->appointment->doctor->first_name . ' ' . $this->appointment->doctor->last_name)
            ->line('**Date and time:** ' . $datetime->format('F j, Y \a\t g:i A'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'doctor_name' => $this->appointment->doctor->first_name . ' ' . $this->appointment->doctor->last_name,
            'start_at' => CarbonImmutable::parse($this->appointment->start_at)
                ->toIso8601String(),
            'message' => 'Your appointment has been rescheduled.',
        ];
    }
}
