<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class UserAccessRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'description',
        'status'
    ];
}
