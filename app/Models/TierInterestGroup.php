<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TierInterestGroup extends Model
{
    use HasFactory;

    protected $table = 'tier_interest_groups';

    protected $fillable = [
        'tier_id',
        'interest_group_main_name',
        'interest_group_name',
        'deleted_at',       // soft delete timestamp (recorded when API stops returning this IG)
        'is_active',        // 1 = active, 0 = soft-deleted/disabled
    ];

    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }
}
