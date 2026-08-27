@php
    $stats = $this->stats();
    $generatedAt = \Illuminate\Support\Carbon::parse($stats['generated_at']);
    $tiles = [
        ['label' => 'Total courses', 'value' => $stats['total']],
        ['label' => 'Published', 'value' => $stats['published']],
        ['label' => 'Drafts', 'value' => $stats['drafts']],
        ['label' => 'With a cover', 'value' => $stats['with_cover']],
    ];
@endphp

<x-filament-panels::page>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($tiles as $tile)
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $tile['label'] }}
                </div>
                <div class="mt-2 text-3xl font-semibold tabular-nums text-gray-950 dark:text-white">
                    {{ number_format($tile['value']) }}
                </div>
            </div>
        @endforeach
    </div>

    {{--
        The point of this panel: the figures above are not read from the
        database on this request. Reload the page and the timestamp does not
        move — it only jumps when a course changes, or when the cache is
        rebuilt from the header action.
    --}}
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Cache status</h3>

        <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Computed</dt>
                <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                    {{ $generatedAt->diffForHumans() }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Store</dt>
                <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                    {{ config('cache.default') }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Expires after</dt>
                <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                    {{ config('cache_layer.ttl.courses', config('cache_layer.ttl.default')) }}s
                </dd>
            </div>
        </dl>

        <div class="mt-4">
            <dt class="text-sm text-gray-500 dark:text-gray-400">Key</dt>
            <dd class="mt-1 overflow-x-auto">
                <code class="text-xs text-gray-950 dark:text-white">{{ $this->cacheKey() }}</code>
            </dd>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                The <code>v</code> segment is the group version. Invalidating the group
                increments it, so every key built afterwards misses and the old entries
                are simply never asked for again.
            </p>
        </div>
    </div>

    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Latest courses</h3>

        @if (empty($stats['latest']))
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No courses yet.</p>
        @else
            <ul class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($stats['latest'] as $course)
                    <li class="flex items-center justify-between py-3">
                        <span class="text-sm text-gray-950 dark:text-white">{{ $course['title'] }}</span>
                        <span @class([
                            'rounded-md px-2 py-1 text-xs font-medium',
                            'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' => $course['is_published'],
                            'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-300' => ! $course['is_published'],
                        ])>
                            {{ $course['is_published'] ? 'Published' : 'Draft' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</x-filament-panels::page>
