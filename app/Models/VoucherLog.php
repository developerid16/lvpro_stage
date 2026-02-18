<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherLog extends Model
{
    protected $table = 'voucher_log_new';

    protected $fillable = [
        'user_id',
        'reward_id',
        'reward_voucher_id',
        'action',
        'receipt_no',
        'qty',
        'from_where'
    ];

    public $timestamps = true;
}
