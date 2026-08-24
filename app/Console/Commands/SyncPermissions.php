<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync
                            {--prune : Also delete permissions that no longer appear in the config}';

    protected $description = 'Reconcile the permissions table with config/permissions.php';

    public function handle(PermissionRegistrar $registrar): int
    {
        $guard = config('permissions.guard');

        /** @var class-string<\Spatie\Permission\Models\Permission> $model */
        $model = config('permission.models.permission');

        $defined = collect(config('permissions.groups'))
            ->flatMap(fn (array $permissions): array => array_keys($permissions))
            ->unique();

        $existing = $model::query()->where('guard_name', $guard)->pluck('name');

        foreach ($defined->diff($existing) as $name) {
            $model::findOrCreate($name, $guard);
            $this->line("  <fg=green>+</> {$name}");
        }

        $stale = $existing->diff($defined);

        if ($stale->isNotEmpty()) {
            if ($this->option('prune')) {
                // Deleting cascades to role_has_permissions, so roles quietly
                // lose the grant along with the permission itself.
                $model::query()->where('guard_name', $guard)->whereIn('name', $stale)->delete();

                foreach ($stale as $name) {
                    $this->line("  <fg=red>-</> {$name}");
                }
            } else {
                $this->newLine();
                $this->warn("{$stale->count()} permission(s) exist in the database but not in the config:");
                $this->line('  '.$stale->implode(', '));
                $this->line('  Run with --prune to remove them.');
            }
        }

        // Without this the next request would still answer from the cached
        // permission map and none of the above would appear to have happened.
        $registrar->forgetCachedPermissions();

        $this->newLine();
        $this->info("Synced {$defined->count()} permission(s) for the [{$guard}] guard.");

        return self::SUCCESS;
    }
}
