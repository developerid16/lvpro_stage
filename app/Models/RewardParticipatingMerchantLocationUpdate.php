<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardParticipatingMerchantLocationUpdate extends Model
{
    use HasFactory, AddsAddedBy;

    protected $table = 'reward_participating_loc_update';
   protected $fillable = [
        'reward_id', 
        'location_id', 
        'club_location_id',
        'participating_merchant_id', 
        'is_selected', 
        'added_by'
    ];
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function participating_merchant()
    {
        return $this->belongsTo(ParticipatingMerchant::class);
    }

    public function participating_location()
    {
        return $this->belongsTo(ParticipatingMerchantLocation::class, 'location_id');
    }


}