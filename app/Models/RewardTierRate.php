<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardTierRate extends Model
{
    use HasFactory;
    protected $fillable = ['reward_id', 'tier_id', 'price'];

}
