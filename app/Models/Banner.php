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
        'status'
    ];
}