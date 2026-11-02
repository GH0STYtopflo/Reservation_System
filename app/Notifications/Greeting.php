<?php

namespace App\Notifications;

use App\Enums\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Greeting extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $profileRoute = match ($notifiable->role) {
            Role::DOCTOR => 'doctors.store',
            Role::PATIENT => 'patients.store',
        };

        return (new MailMessage)
            ->subject('Welcome to '.config('app.name').'!')
            ->greeting('Hello')
            ->line('Thank you for joining '.config('app.name').'.')
            ->line('We are glad to have you with us!')
            ->line('Please complete your profile to get started. Send a POST request here because we don\'t have a frontend yet')
            ->action('Complete Profile', route($profileRoute))
            ->salutation('Best regards, '.config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome!',
            'message' => 'Welcome to '.config('app.name').'! Please complete your profile.',
            'action_url' => route(
                $notifiable->role === Role::DOCTOR
                    ? 'doctors.store'
                    : 'patients.store'
            ),
            'action_text' => 'Complete Profile',
        ];
    }
}
