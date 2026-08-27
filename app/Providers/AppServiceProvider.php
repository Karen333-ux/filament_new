<?php

namespace App\Providers;

use App\Models\Course;
use App\Observers\CourseObserver;
use App\Observers\MediaObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

        // Cache invalidation. Media is a vendor model and cannot carry an
        // #[ObservedBy] attribute, so both registrations live here rather than
        // being split between a model attribute and this provider.
        Course::observe(CourseObserver::class);
        Media::observe(MediaObserver::class);

        // The super admin passes every check. Returning null rather than false
        // for everyone else leaves the normal policy chain to decide — without
        // that distinction this callback would deny the whole application.
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole(config('permissions.super_admin_role')) ? true : null;
        });
    }
}
