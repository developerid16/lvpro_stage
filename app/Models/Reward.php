<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Maatwebsite\Excel\Facades\Excel;

class Reward extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'voucher_image',
        'voucher_detail_img',
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
        'where_use',  
        'inventory_qty',
        'inventory_type',
        'voucher_value' ,  
        'voucher_set' , 
        'set_qty', 
        'clearing_method' ,  
        'csvFile',
        'type',
        'direct_utilization',
        'category_id',
        'month',
        'club_location',
        'from_month',
        'to_month',
        'cso_method',
        'is_draft',
        'days',
        'start_time',
        'end_time',
        'suspend_deal',
        'suspend_voucher',
        'purchased_qty',
        'status'
        
    ];

    protected $casts = [
        'sales_start_date' => 'date',
        'sales_end_date' => 'date',
        'voucher_validity' => 'date',
        'publish_start_date' => 'date',
        'publish_end_date'   => 'date',  
        'days' => 'array',    

    ];



    public static function getRewardTypeLabel(int $type): string
    {
        return match ($type) {
            0 => 'Treats & Deals',
            1 => 'eVoucher',
            2 => 'Birthday Voucher',
            default => '',
        };
    }


    public function setLabelsAttribute($value)
    {
        $this->attributes['labels'] = implode(',', $value);
    }

    public function getLabelsAttribute($value)
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

    private function validateVoucherFileStructure($filePath)
    {
        $rows = Excel::toArray([], $filePath);

        // File empty
        if (empty($rows) || empty($rows[0])) {
            return 'File is empty.';
        }

        $sheet = $rows[0];

        // Must have at least header row
        if (!isset($sheet[0])) {
            return 'Invalid file format.';
        }

        $header = array_map('trim', $sheet[0]);

        // ❌ More than 1 column
        if (count($header) !== 1) {
            return 'File must contain only one column named "code".';
        }

        // ❌ Header must be exactly "code"
        if (strtolower($header[0]) !== 'code') {
            return 'File column name must be "code".';
        }

        return null; // ✅ valid
    }

}
