<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use App\Observers\UserObserver;
use Gate;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // We can be responsible about the data we feed into our model instances
        Model::unguard();

        // Register the user observer
        User::observe(UserObserver::class);

        // --------------------------------------------------------------------------------------
        // ---------------------------- Tier 1 authorization ------------------------------------
        // --------------------------------------------------------------------------------------
        Gate::define('admin-access', function (User $user) {
            return $user->role === Role::ADMIN ? Response::allow() : Response::deny();
        });

        Gate::define('doctor-access', function (User $user) {
            return $user->role === Role::DOCTOR ? Response::allow() : Response::deny();
        });

        Gate::define('patient-access', function (User $user) {
            return $user->role === Role::PATIENT ? Response::allow() : Response::deny();
        });
        // --------------------------------------------------------------------------------------

        Gate::define('profile-exists', function (User $user) {
            if ($user->role === Role::DOCTOR && ! $user->doctorProfile()->exists()) {
                return Response::deny('You must complete your profile before accessing this endpoint.');
            }

            if ($user->role === Role::PATIENT && ! $user->patientProfile()->exists()) {
                return Response::deny('You must complete your profile before accessing this endpoint.');
            }

            return Response::allow();
        });

        Gate::define('doctor-profile-verified', function (User $user) {
            if ($user->role === Role::DOCTOR && $user->doctorProfile->verified_at === null) {
                return Response::deny('Your profile is not verified');
            }

            return Response::allow();
        });
    }
}
