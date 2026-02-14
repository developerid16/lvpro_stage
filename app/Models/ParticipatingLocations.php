<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParticipatingLocations extends Model
{
    use HasFactory, AddsAddedBy;
    protected $fillable = [      
        
        'reward_id',
        'club_location_id',  // MUST exist
        'participating_merchant_id',
        'location_id',
        'is_selected',
        'added_by',

    ];
    public $table = 'reward_participating_locations';
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
