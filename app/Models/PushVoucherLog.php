<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushVoucherLog extends Model
{
    protected $table = 'push_voucher_logs';

    protected $fillable = [
        'user_id',
        'reward_id',
        'push_voucher_member_id',
        'type',
        'status',
        'message',
        'push_by'
    ];
}