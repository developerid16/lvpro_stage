<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    use SoftDeletes, AddsAddedBy;
    protected $fillable = [
        'name',
        'logo',
        'status',
        'active_department_id',
        'active_club_location_id',
        'active_role_id'
    ];
}

