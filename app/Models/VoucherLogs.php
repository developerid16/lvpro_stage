<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherLogs extends Model
{
    use HasFactory;
    protected $table = 'voucher_log_new';

    protected $fillable = [
        'user_id',
        'reward_id',
        'reward_voucher_id',
        'action',
        'receipt_no',
        'qty',
        'from_where',
    ];

    public $timestamps = true;
    // VoucherLogs.php// App\Models\VoucherLogs.php
    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id', 'id');
    }



}
