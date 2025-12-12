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
        'redemption_start_date',
        'redemption_start_time',
        'redemption_end_date',
        'redemption_end_time',
        'type',
        'publish_channels',
        'card_types',
        'dependent_types',
        'marital_status',
        'gender',

        'age_mode',
        'age_from',
        'age_to',

    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
