<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TierMemberType extends Model
{
    use HasFactory;

    protected $table = 'tier_member_types';

    protected $fillable = [
        'tier_id',
        'membership_type_code',
        'deleted_at',    // soft delete timestamp (recorded when API stops returning this type)
        'is_active',     // 1 = active, 0 = soft-deleted/disabled
    ];

    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }
}
