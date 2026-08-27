<?php

namespace App\Console\Commands;

use App\Support\Cache\CacheLayer;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    protected $signature = 'cache:warm
                            {--invalidate : Retire the current entries before rebuilding}';

    protected $description = 'Rebuild the application cache layer entries listed in config/cache_layer.warmers';

    public function handle(CacheLayer $cache): int
    {
        $warmers = config('cache_layer.warmers', []);

        if ($warmers === []) {
            $this->components->warn('No warmers are configured.');

            return self::SUCCESS;
        }

        foreach ($warmers as $class) {
            $warmer = app($class);

            if (! method_exists($warmer, 'warm')) {
                $this->components->error("{$class} has no warm() method.");

                return self::FAILURE;
            }

            if ($this->option('invalidate') && defined($class.'::GROUP')) {
                $cache->invalidate($class::GROUP);
            }

            $started = microtime(true);

            $warmer->warm();

            $this->components->twoColumnDetail(
                class_basename($class),
                number_format((microtime(true) - $started) * 1000, 1).' ms',
            );
        }

        $this->components->info(count($warmers).' warmer(s) run.');

        return self::SUCCESS;
    }
}
