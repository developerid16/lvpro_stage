<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'model',
        'model_id',
        'description',
        'ip_address',
        'user_agent',
    ];
}
