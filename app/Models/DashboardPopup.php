<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DashboardPopup extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [ 'optout', 'button', 'name', 'description', 'start_date', 'end_date', 'optout', 'slider_order','frequency','order'];
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
