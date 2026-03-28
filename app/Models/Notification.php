<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'img',
        'short_desc',
        'desc',
        'date',
        'type',
        'user_id',
        'reward_id',
        'added_by',

        'active_department_id',
        'active_club_location_id',
        'active_role_id'
    ];
     public $table = 'notifications';
}
