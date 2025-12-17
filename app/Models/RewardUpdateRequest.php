<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardUpdateRequest extends Model
{
    use HasFactory;
  

    protected $table = "reward_update_requests";
    protected $fillable = [
    'reward_id',
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
    'fabs_category_id',
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
    'status',
    'from_month',
    'to_month',
    'request_by'
        
    
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'voucher_validity' => 'date',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'request_by');
    }

}
