<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'receipt_no',
        'reward_id',
        'member_id',
        'member_name',
        'member_email',
        'qty',
        'status',
        'payment_mode',
        'note',
        'update_membership',
        'collection',
        'subtotal',
        'admin_fee',
        'total',
        'remark',
        'redeemed_at',
        'unique_code',
        'file'
    ];

    public $table = 'purchases';

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

     protected $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'redeemed_at' => 'datetime',
    ];

}

