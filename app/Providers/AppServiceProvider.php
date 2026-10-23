<?php

namespace App\Providers;

use App\Models\City;
use App\Policies\CityPolicy;
use App\Role;
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


        //--------------------------------------------------------------------------------------
        //---------------------------- Tier 1 authorization ------------------------------------
        //--------------------------------------------------------------------------------------
        Gate::define('admin-access', function ($user) {
            return $user->role === Role::ADMIN ? Response::allow() : Response::denyAsNotFound();
        });

        Gate::define('doctor-access', function ($user) {
            return $user->role === Role::DOCTOR ? Response::allow() : Response::denyAsNotFound();
        });
        //--------------------------------------------------------------------------------------
    }
}
