<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reward extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code', 'name', 'description', 'no_of_keys', 'quantity', 'start_date', 'end_date', 'image_1', 'image_2', 'status', 'total_redeemed', 'company_name', 'term_of_use', 'how_to_use', 'labels', 'is_featured', 'type', 'expiry_day', 'reward_type', 'amount', 'product_name', 'countdown', 'end_time', 'start_time', 'days', 'brand_name','sku', 'parent_type', 'image_3'






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
        if($value){
            $this->attributes['days'] = implode(',', $value);
            
        }
    }

    public function getDaysAttribute($value)
    {
        return array_filter(explode(",", $value));
    }
}
