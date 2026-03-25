<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fabs extends Model
{
    use SoftDeletes;
    protected $table = 'fabs';

    protected $fillable = [
        'name',
        'code',
        'status',
        'added_by'
    ];
}
