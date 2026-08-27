<?php

namespace App\Observers;

use App\Models\Course;
use App\Services\CourseStats;
use App\Support\Cache\CacheLayer;

/**
 * Event-driven invalidation: the moment a course changes, everything cached
 * about courses is retired.
 *
 * saved() covers both create and update, so a course being published — which
 * moves a number from drafts to published without touching the total — is
 * caught as surely as a new row is.
 */
class CourseObserver
{
    public function __construct(protected CacheLayer $cache) {}

    public function saved(Course $course): void
    {
        $this->invalidate();
    }

    public function deleted(Course $course): void
    {
        $this->invalidate();
    }

    protected function invalidate(): void
    {
        $this->cache->invalidate(CourseStats::GROUP);
    }
}
