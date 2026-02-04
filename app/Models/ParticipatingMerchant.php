<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipatingMerchant extends Model
{
    use HasFactory, AddsAddedBy;
    protected $table = 'participating_merchants';

    protected $fillable = [
        'name',
        'status',
        'added_by'
    ];
}
