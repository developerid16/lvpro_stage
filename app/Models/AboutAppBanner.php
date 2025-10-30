<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutAppBanner extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['image', 'status'];
    // public function getImageAttribute($value)
    // {
    //     if ($value) {
    //         return asset($value);
    //     }
    //     return $value;
    // }
}
