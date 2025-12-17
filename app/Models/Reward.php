<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reward extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'voucher_image',
        'name',
        'description',
        'term_of_use',
        'how_to_use',
        'merchant_id',
        'reward_type',
        'usual_price',
        'publish_start_date',
        'publish_start_time',
        'publish_end_date',
        'publish_end_time',
        'sales_start_date',
        'sales_start_time',
        'sales_end_date',
        'sales_end_time',
        'max_quantity',
        'low_stock_1',
        'low_stock_2',
        'friendly_url',
        'category_id',
        'club_classification_id',
        'fabs_category_id',
        'smc_classification_id',
        'ax_item_code',
        'publish_independent',
        'publish_inhouse',
        'send_reminder',       
        'hide_quantity', 
         
        'max_order',     
        'location_text',     
        'participating_merchant_id',     
        'voucher_validity',  
        'inventory_qty',
        'inventory_type',
        'voucher_value' ,  
        'voucher_set' ,  
        'clearing_method' ,  
        'csvFile',
        'type',
        'direct_utilization',
        'category_id',
        'month',
        'club_location',
        'from_month',
        'to_month',
        
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'voucher_validity' => 'date',
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

    public function rewardLocations()
    {
        return $this->hasMany(RewardLocation::class);
    }
    public function participatingLocations()
    {
        return $this->hasMany(ParticipatingLocations::class);
    }


}
