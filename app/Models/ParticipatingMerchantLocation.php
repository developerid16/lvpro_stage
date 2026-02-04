<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipatingMerchantLocation extends Model
{
    use HasFactory, AddsAddedBy;


    protected $table = 'participating_merchant_location';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'code',
        'participating_merchant_id',
        'club_location_id',
        'status',
        'added_by'
    ];

   protected $casts = [
    'start_date' => 'datetime',
    'end_date'   => 'datetime',
];

    // Relations
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function clubLocation()
    {
        return $this->belongsTo(ClubLocation::class);
    }

}