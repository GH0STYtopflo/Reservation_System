<?php

use App\Enums\AvailabilityDefType;
use App\Enums\Role;
use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\Unavailability;
use App\Models\User;
use App\Notifications\AppointmentDueTomorrow;
use Illuminate\Console\Scheduling\Schedule as DayOfWeekSchedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    DB::transaction(function () {
        User::where('created_at', '<', now()->subDays(7))
            ->where('role', Role::DOCTOR->value)
            ->whereDoesntHave('doctorProfile')
            ->delete();

        User::where('created_at', '<', now()->subDays(7))
            ->where('role', Role::PATIENT->value)
            ->whereDoesntHave('patientProfile')
            ->delete();
    });
})
    ->name('Prune profile-less users')
    ->description('Delete users who signed up but did not complete their profile within a week.')
    ->dailyAt('00:00');


Schedule::call(function () {
    Availability::where('type', AvailabilityDefType::ONCE)
        ->where('date', '<', today())
        ->delete();
})
    ->name('Delete expired one-time availabilities')
    ->description('Delete expired one-time availabilities')
    ->weeklyOn(DayOfWeekSchedule::FRIDAY);

Schedule::call(function () {
    Unavailability::where('end_at', '<', today())
        ->delete();
})
    ->name('Delete expired unavailabilities')
    ->description('Delete expired unavailabilities')
    ->weeklyOn(DayOfWeekSchedule::FRIDAY);

Schedule::call(function () {
    $appointments = Appointment::whereDate('start_at', today()->addDay())->get();

    foreach ($appointments as $appointment) {
        SendAppointmentNotification::dispatch($appointment->patient->user, $appointment);
    }
})
    ->name('Appointment reminder')
    ->description('Send appointment reminder to patients a day prior to their appointment')
    ->dailyAt('00:00');
