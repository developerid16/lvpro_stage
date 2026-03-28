<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentActivityLog extends Model
{
    protected $table = 'department_activity_logs';

    protected $fillable = [
        'user_id', 'user_name',
        'action', 'module', 'record_id', 'record_label',
        'old_values', 'new_values', 'changed_fields','message',
        'department_id', 'department_name',
        'role_id', 'role_name',
        'ip_address', 'user_agent', 'url', 'method',
    ];

    protected $casts = [
        'old_values'     => 'array',
        'new_values'     => 'array',
        'changed_fields' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }
}