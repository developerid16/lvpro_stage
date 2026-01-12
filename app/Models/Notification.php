<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'img',
        'short_desc',
        'desc',
        'date',
        'type',
    ];
     public $table = 'notifications';
}
