<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentDueTomorrow extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $doctor = $this->appointment->doctor;

        return (new MailMessage)
            ->subject('Appointment Reminder - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder that you have an appointment scheduled for tomorrow.')
            ->line('**Appointment Details:**')
            ->line('Doctor: ' . $doctor->name)
            ->line('Date: ' . $this->appointment->start_at->format('l, F j, Y'))
            ->line('Time: ' . $this->appointment->start_at->format('H:i'))
            ->line('Please make sure to arrive on time.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Appointment Reminder',
            'message' => 'You have an appointment scheduled for tomorrow.',
            'appointment_id' => $this->appointment->id,
            'doctor' => $this->appointment->doctor->name,
            'start_at' => $this->appointment->start_at->toIso8601String(),
        ];
    }
}
