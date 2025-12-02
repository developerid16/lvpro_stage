<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardDates extends Model
{
    protected $table = 'reward_dates';

    protected $fillable = [
        'reward_id',
        'merchant_id',
        'publish_start_date',
        'publish_start_time',
        'publish_end_date',
        'publish_end_time',
        'sales_start_date',
        'sales_start_time',
        'sales_end_date',
        'sales_end_time',
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
   
}
