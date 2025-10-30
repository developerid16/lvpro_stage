<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReferral extends Model
{
    use HasFactory;
    protected $fillable = [
        'referral_by', 'referral_to', 'status'
    ];
    protected $table = "user_referral";
    /**
     * Get the user that owns the UserPurchasedReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function touser(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'referral_to', 'id');
    }
    public function byuser(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'referral_by', 'id');
    }
}
