<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardVoucher extends Model
{
    use HasFactory;

    protected $table = 'reward_vouchers';

    protected $fillable = [
        'reward_id',
        'code',
        'is_used',
        'type'
    ];

    /**
     * A voucher belongs to a reward.
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Check if voucher is unused.
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', 0);
    }

    /**
     * Check if voucher is used.
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', 1);
    }
}
