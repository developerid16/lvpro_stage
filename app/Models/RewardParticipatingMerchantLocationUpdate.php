<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardParticipatingMerchantLocationUpdate extends Model
{
    use HasFactory;

    protected $table = 'reward_participating_loc_update';
   protected $fillable = [
        'reward_id', 
        'location_id', 
        'participating_merchant_id', 
        'is_selected', 
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