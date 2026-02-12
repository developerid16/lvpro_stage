<?php

namespace App\Models;

use App\Traits\AddsAddedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardTierRate extends Model
{
    use HasFactory, AddsAddedBy;
    protected $fillable = ['reward_id', 'tier_id', 'price','added_by'];

   public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id', 'id');
    }

}
