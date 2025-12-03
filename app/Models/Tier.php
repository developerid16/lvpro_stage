<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tier extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();

        // Order by t_order ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('t_order', 'asc');
        });
    }
    protected $fillable = ['alias_name','tier_name', 'instore_multiplier', 'isc_multiplier', 'spend_amount', 't_order', 'detail', 'image'];


    public function milestones(): HasMany
    {
        return $this->hasMany(TierMilestone::class);
    }
    public function getImageUrlAttribute()
    {

        return asset("images/$this->image");
    }
    public function rewardRates()
    {
        return $this->hasMany(RewardTierRate::class);
    }

}
