<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipatingMerchant extends Model
{
    protected $table = 'participating_merchants';

    protected $fillable = [
        'name',
        'status',
    ];
}
