<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignVoucherGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'campaign_voucher_groups';
    protected $fillable = [
        'name','reward_count'
    ];
    /**
     * Get all of the users for the CampaignVoucherGroup
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(GroupUser::class, 'group_id','id');
    }
}
