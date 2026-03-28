<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'department_role',
            'role_id',
            'department_id'
        );
    }
}