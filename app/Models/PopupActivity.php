<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PopupActivity extends Model
{
    use HasFactory;
    protected $fillable = [ 'popup_id', 'date', 'is_optout', 'user_id'];
    // public function getImageAttribute($value)
    // {
    //     if ($value) {
    //         return asset($value);
    //     }
    //     return $value;
    // }
    protected $casts = [
        'date' => 'date',
    ];
}
