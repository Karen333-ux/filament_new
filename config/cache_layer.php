<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application cache layer
    |--------------------------------------------------------------------------
    |
    | Settings for App\Support\Cache\CacheLayer, the read-through cache the
    | application reads its expensive aggregates through.
    |
    */

    /*
    | Turning this off makes every remember() call fall straight through to its
    | callback. Useful when a stale value is suspected: flip it, reproduce, and
    | you know immediately whether the cache is the culprit.
    */
    'enabled' => env('CACHE_LAYER_ENABLED', true),

    /*
    | Namespace for every key this layer writes, so a `cache:clear` blast radius
    | is obvious and the keys are recognisable in the cache table.
    */
    'prefix' => 'cl',

    /*
    |--------------------------------------------------------------------------
    | Time to live, in seconds, per group
    |--------------------------------------------------------------------------
    |
    | Invalidation is event-driven, so these are a safety net rather than the
    | main mechanism: they bound how long a stale value can survive if an
    | invalidation is ever missed. Groups absent from this list use `default`.
    |
    */
    'ttl' => [
        'default' => 3600,
        'courses' => 900,
    ],

    /*
    |--------------------------------------------------------------------------
    | Warmers
    |--------------------------------------------------------------------------
    |
    | Classes run by `php artisan cache:warm`. Each must expose a warm() method
    | that populates its own entries. They run after deploy and on a schedule,
    | so the first visitor after an invalidation gets a hit rather than paying
    | for the rebuild.
    |
    */
    'warmers' => [
        App\Services\CourseStats::class,
    ],

];
