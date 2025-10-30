<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['url',  'image', 'status', 'code', 'name', 'description', 'start_date', 'end_date', 'slider_type', 'slider_order'];
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
