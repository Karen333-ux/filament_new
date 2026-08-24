<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission catalogue
    |--------------------------------------------------------------------------
    |
    | The single source of truth for every permission this application knows
    | about. `php artisan permissions:sync` reconciles the permissions table
    | against this file, and the role form builds its checkbox matrix from the
    | same groups — so adding a line here is the only edit needed for a new
    | permission to exist in the database and appear in the UI.
    |
    | Keys are the permission names used in policies and `$user->can()`.
    | Values are the labels shown to whoever is building a role.
    |
    */

    'guard' => 'web',

    /*
    | A role with this name passes every permission check. Gate::before() in
    | AppServiceProvider short-circuits on it, which is what keeps the first
    | administrator from locking themselves out of the roles page.
    */
    'super_admin_role' => 'super-admin',

    'groups' => [

        'Courses' => [
            'course.viewAny' => 'Browse courses',
            'course.view' => 'View a course',
            'course.create' => 'Create courses',
            'course.update' => 'Edit courses',
            'course.delete' => 'Delete courses',
        ],

        'Roles' => [
            'role.viewAny' => 'Browse roles',
            'role.view' => 'View a role',
            'role.create' => 'Create roles',
            'role.update' => 'Edit roles',
            'role.delete' => 'Delete roles',
        ],

    ],

];
