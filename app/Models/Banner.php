<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'header',
        'button_text',
        'link',
        'description',
        'desktop_image',
        'mobile_image',
        'status',
        'added_by',
        'active_department_id',
        'active_club_location_id',
        'active_role_id'
    ];
}