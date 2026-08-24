<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // The super admin passes every check. Returning null rather than false
        // for everyone else leaves the normal policy chain to decide — without
        // that distinction this callback would deny the whole application.
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole(config('permissions.super_admin_role')) ? true : null;
        });
    }
}
