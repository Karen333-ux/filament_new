<?php

namespace App\Observers;

use App\Models\Course;
use App\Services\CourseStats;
use App\Support\Cache\CacheLayer;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Uploading a cover changes the with_cover figure without ever writing to the
 * courses table, so CourseObserver never fires for it. Media rows carry the
 * model they belong to, which is enough to invalidate the right group.
 */
class MediaObserver
{
    public function __construct(protected CacheLayer $cache) {}

    public function saved(Media $media): void
    {
        $this->invalidate($media);
    }

    public function deleted(Media $media): void
    {
        $this->invalidate($media);
    }

    protected function invalidate(Media $media): void
    {
        if ($media->model_type === Course::class) {
            $this->cache->invalidate(CourseStats::GROUP);
        }
    }
}
