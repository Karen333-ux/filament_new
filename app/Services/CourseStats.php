<?php

namespace App\Services;

use App\Models\Course;
use App\Support\Cache\CacheLayer;
use Illuminate\Support\Facades\DB;

/**
 * The course figures shown on the overview page.
 *
 * Read straight from the database this is four aggregate queries plus a join
 * against the media table on every page view, for numbers that only change when
 * somebody edits a course. That is the shape of thing worth caching.
 */
class CourseStats
{
    public const GROUP = 'courses';

    public const KEY = 'stats';

    public function __construct(protected CacheLayer $cache) {}

    /**
     * @return array{total:int,published:int,drafts:int,with_cover:int,latest:array,generated_at:string}
     */
    public function get(): array
    {
        return $this->cache->remember(self::GROUP, self::KEY, fn () => $this->compute());
    }

    /**
     * Populate the entry without anybody having to ask for it. Called by
     * `cache:warm` after a deploy and on the schedule.
     */
    public function warm(): void
    {
        $this->cache->forget(self::GROUP, self::KEY);

        $this->get();
    }

    public function isCached(): bool
    {
        return $this->cache->has(self::GROUP, self::KEY);
    }

    protected function compute(): array
    {
        $counts = Course::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when is_published then 1 end) as published')
            ->first();

        $withCover = DB::table('media')
            ->where('model_type', Course::class)
            ->where('collection_name', 'cover')
            ->distinct()
            ->count('model_id');

        $total = (int) ($counts->total ?? 0);
        $published = (int) ($counts->published ?? 0);

        return [
            'total' => $total,
            'published' => $published,
            'drafts' => $total - $published,
            'with_cover' => $withCover,
            'latest' => Course::query()
                ->latest('id')
                ->limit(5)
                ->get(['id', 'title', 'is_published'])
                ->map(fn (Course $course): array => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'is_published' => $course->is_published,
                ])
                ->all(),

            // Stamped inside the callback, so it records when the value was
            // built rather than when it was read. The overview page shows it,
            // which is what makes a cache hit visible instead of theoretical.
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
