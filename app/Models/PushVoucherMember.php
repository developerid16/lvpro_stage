<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushVoucherMember extends Model
{
    protected $table = 'push_voucher_member';

    protected $fillable = [
        'reward_id',
        'member_id',
        'file',
        'type',
        'interest_group',
        'publish_channels',
        'card_types',
        'dependent_types',
        'marital_status',
        'gender',
        'age_mode',
        'age_from',
        'age_to',
        'zones',
        'membership_joining_from_date',
        'membership_joining_to_date',
        'membership_expiry_from_date',
        'membership_expiry_to_date',
        'membership_renewable_from_date',
        'membership_renewable_to_date',
        'method'
    ];

    // ✅ Auto encode/decode
    protected $casts = [
        'interest_group'   => 'array',
        'publish_channels' => 'array',
        'card_types'       => 'array',
        'marital_status'   => 'array',
        'gender'           => 'array',
        'zones'            => 'array',
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
    
}
