<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipatingMerchant extends Model
{
    use HasFactory, AddsAddedBy, SoftDeletes;
    protected $table = 'participating_merchants';

    protected $fillable = [
        'name',
        'status',
        'department_id',
        'added_by'
    ];

      public function locations()
    {
        return $this->hasMany(
            ParticipatingMerchantLocation::class,
            'participating_merchant_id'
        );
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
