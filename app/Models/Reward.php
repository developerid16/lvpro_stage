<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reward extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'description',
        'no_of_keys',
        'quantity',
        'start_date',
        'end_date',
        'image_1',
        'image_2',
        'status',
        'total_redeemed',
        'term_of_use',
        'how_to_use',
        'labels',
        'is_featured',
        'type',
        'expiry_day',
        'reward_type',
        'amount',
        'product_name',
        'countdown',
        'end_time',
        'start_time',
        'days',
        'sku',
        'parent_type',
        'image_3',
        'company_id',
        'location_ids',
        'clearing_method',
        'inventory_type',
        'inventory_qty',
        'voucher_value',
        'voucher_set',
        'csvFile'

    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function setLabelsAttribute($value)
    {
        $this->attributes['labels'] = implode(',', $value);
    }

    public function getLabelsAttribute($value)
    {
        return array_filter(explode(",", $value));
    }
    public function setDaysAttribute($value)
    {
        $this->attributes['days'] = null;
        if ($value) {
            $this->attributes['days'] = implode(',', $value);
        }
    }

    public function getDaysAttribute($value)
    {
        return array_filter(explode(",", $value));
    }

    public function setLocationIdsAttribute($value)
    {
        $this->attributes['location_ids'] = null;
        if ($value && is_array($value)) {
            $this->attributes['location_ids'] = json_encode(array_filter($value));
        }
    }

    public function getLocationIdsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Get the company that owns the reward
     */
    public function company()
    {
        return $this->belongsTo(PartnerCompany::class, 'company_id');
    }

    /**
     * Get the locations for this reward
     */
    public function locations()
    {
        $locationIds = $this->location_ids;
        if (empty($locationIds)) {
            return collect([]);
        }
        return Location::whereIn('id', $locationIds)->get();
    }

    public function rewardDates()
    {
        return $this->hasMany(\App\Models\RewardDates::class);
    }

    public function tierRates()
    {
        return $this->hasMany(RewardTierRate::class);
    }


}
