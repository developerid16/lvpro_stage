<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignVoucherLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log', 'admin_id', 'reward_id'
    ];

}
