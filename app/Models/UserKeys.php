<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserKeys extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'available_key',
    ];


    protected $table = "app_user_keys";
}
