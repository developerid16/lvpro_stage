<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fabs extends Model
{
    protected $table = 'fabs';

    protected $fillable = [
        'name',
        'code',
        'status',
    ];
}
