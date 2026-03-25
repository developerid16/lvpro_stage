<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tiers';

    // NOTE: t_order column has been DROPPED from tiers table.
    // The old boot() global scope ordering by t_order has been removed.

    protected $fillable = [
        'code',
        'tier_name',
        'status',
        'added_by'
    ];

    /**
     * Interest Groups linked to this Tier
     */
    public function interestGroups()
    {
        return $this->hasMany(TierInterestGroup::class, 'tier_id');
    }

    /**
     * Member Types linked to this Tier
     */
    public function memberTypes()
    {
        return $this->hasMany(TierMemberType::class, 'tier_id');
    }

    /**
     * Reward Tier Rates (existing relationship - kept as-is)
     */
    public function rewardRates()
    {
        return $this->hasMany(RewardTierRate::class);
    }
}