<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Spatie's Role, re-exported under App\Models so Filament's generators and
 * policy discovery treat it like any other application model. config/permission.php
 * points `models.role` here, so both packages resolve the same class.
 */
class Role extends SpatieRole
{
}
