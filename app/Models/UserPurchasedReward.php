<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPurchasedReward extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'reward_id', 'key_use', 'expiry_date', 'unique_no', 'status', 'get_from', 'meta_data', 'redeem_date', 'voucher_serial', 'reason', 'reward_type'
    ];

    protected $table = "user_purchased_reward";
    protected $casts = [
        'expiry_date' => 'datetime',
        'redeem_date' => 'datetime',
    ];
    /**
     * Get the user that owns the UserPurchasedReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'id');
    }
    /**
     * Get the reward that owns the UserPurchasedReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'reward_id', 'id')->withTrashed();
    }
}
