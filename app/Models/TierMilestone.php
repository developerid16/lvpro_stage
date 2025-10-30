<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TierMilestone extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();

        // Order by t_order ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('amount', 'asc');
        });
    }
    protected $fillable = ['tier_id', 'name', 'amount', 'type', 'no_of_keys', 'reward_id', 'min', 'max'];
}
