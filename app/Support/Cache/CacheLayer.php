<?php

namespace App\Support\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * A read-through cache whose entries are grouped, and whose groups can be
 * invalidated as a unit.
 *
 * Laravel's own answer to "invalidate everything about courses" is cache tags,
 * but tags exist only on the redis and memcached stores. This application runs
 * on CACHE_STORE=database, so tags would fail at runtime the moment anything
 * called flush() on them.
 *
 * Instead each group carries a version number that is part of every key it
 * writes:
 *
 *     cl:courses:v7:stats
 *
 * Invalidating the group increments that counter. Every key built afterwards
 * points at v8, so the v7 entries are unreachable — no scan of the cache store,
 * no tag index, and it behaves identically on every driver.
 *
 * The trade-off is that the v7 rows are not deleted, they are only orphaned;
 * they occupy space until their TTL expires. That is what the ttl settings in
 * config/cache_layer.php bound, and it is why those values are minutes rather
 * than days.
 */
class CacheLayer
{
    public function remember(string $group, string $key, Closure $callback): mixed
    {
        if (! config('cache_layer.enabled', true)) {
            return $callback();
        }

        return $this->store()->remember(
            $this->key($group, $key),
            $this->ttl($group),
            $callback,
        );
    }

    /**
     * Retire every entry in the group. Callers do not need to know which keys
     * exist, which is the point — an observer can invalidate "courses" without
     * knowing that a stats payload and a navigation badge both live there.
     */
    public function invalidate(string $group): void
    {
        $key = $this->versionKey($group);

        // add() writes only when the key is absent. If it succeeds there was no
        // version yet, so nothing was ever cached under this group and there is
        // nothing to retire. If it fails the key exists and we move it on.
        if ($this->store()->add($key, 1)) {
            return;
        }

        $this->store()->increment($key);
    }

    public function version(string $group): int
    {
        $key = $this->versionKey($group);

        $version = $this->store()->get($key);

        if (! is_numeric($version)) {
            // Two processes can reach here at once. add() lets exactly one of
            // them write, and the other reads the winner's value on the next
            // line, so they cannot disagree about the current version.
            $this->store()->add($key, 1);

            $version = $this->store()->get($key) ?? 1;
        }

        return (int) $version;
    }

    public function key(string $group, string $key): string
    {
        return implode(':', [
            config('cache_layer.prefix', 'cl'),
            $group,
            'v'.$this->version($group),
            $key,
        ]);
    }

    public function has(string $group, string $key): bool
    {
        return $this->store()->has($this->key($group, $key));
    }

    public function forget(string $group, string $key): void
    {
        $this->store()->forget($this->key($group, $key));
    }

    protected function ttl(string $group): int
    {
        return (int) config(
            "cache_layer.ttl.{$group}",
            config('cache_layer.ttl.default', 3600),
        );
    }

    /**
     * The version counter itself is stored forever. Letting it expire would
     * reset the group to v1 and silently resurrect entries retired long ago.
     */
    protected function versionKey(string $group): string
    {
        return config('cache_layer.prefix', 'cl').':version:'.$group;
    }

    protected function store(): Repository
    {
        return Cache::store();
    }
}
