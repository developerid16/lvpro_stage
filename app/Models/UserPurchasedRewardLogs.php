<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPurchasedRewardLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'admin_id', 'action', 'reason', 'full_str', 'keys', 'user_id', 'reward_id'
    ];

    protected $table = "user_purchased_reward_logs";

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
