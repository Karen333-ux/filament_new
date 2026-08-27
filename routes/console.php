<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Invalidation is event-driven, so this is not what keeps the figures correct —
| it is the backstop. If an invalidation is ever missed, or an entry expires
| during a quiet hour, this rebuilds it before anyone arrives to find a cold
| cache. withoutOverlapping() matters because the scheduler runs in the same
| container as the queue workers on a one-core box.
*/
Schedule::command('cache:warm')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground();
