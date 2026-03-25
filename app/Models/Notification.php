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
        'added_by'
    ];
     public $table = 'notifications';
}
