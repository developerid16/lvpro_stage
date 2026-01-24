<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'reward_id', 
        'location_id', 
        'merchant_id', 
        'inventory_qty', 
        'is_selected', 
        'total_qty',

    ];
  public function reward()
  {
      return $this->belongsTo(Reward::class);
  }

  public function merchant()
  {
      return $this->belongsTo(Merchant::class);
  }

  public function location()
  {
      return $this->belongsTo(ClubLocation::class, 'location_id');
  }


}
