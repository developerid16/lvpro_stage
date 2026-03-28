<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DashboardPopup extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [  'button', 'name',
     'start_date', 'end_date', 'frequency','order', 'desktop_image',  'mobile_image','url',

    'added_by',
    'active_department_id',
    'active_club_location_id',
    'active_role_id'
     ];
    // public function getImageAttribute($value)
    // {
    //     if ($value) {
    //         return asset($value);
    //     }
    //     return $value;
    // }
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}
