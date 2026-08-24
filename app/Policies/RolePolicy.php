<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * Every check here reads a permission name straight out of
 * config/permissions.php, so what a role may do is decided by the catalogue
 * and the role builder rather than by code. Laravel discovers this class by
 * name, and Filament runs the resource's pages and actions through it.
 *
 * Gate::before in AppServiceProvider lets the super admin past all of these.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.viewAny');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role.view');
    }

    public function create(User $user): bool
    {
        return $user->can('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('role.delete');
    }

    /**
     * Filament checks this before offering the bulk-delete action.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('role.delete');
    }
}
