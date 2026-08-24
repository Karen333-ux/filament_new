<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/**
 * Mirrors RolePolicy: each method resolves to a permission name declared in
 * config/permissions.php, so course access is granted by ticking boxes on the
 * roles page rather than by editing code.
 */
class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('course.viewAny');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can('course.view');
    }

    public function create(User $user): bool
    {
        return $user->can('course.create');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('course.update');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('course.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('course.delete');
    }
}
