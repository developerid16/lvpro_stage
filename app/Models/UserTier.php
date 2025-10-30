<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserTier extends Model
{

    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id', 'tier_id', 'status', 'meta_data', 'end_at', 'reach_at', 'amount_spend'
    ];

    protected $casts = [
        'end_at' => 'datetime',
        'reach_at' => 'datetime',
    ];
    public function getMetaDataAttribute($value)
    {
        if ($value) {
            return collect(json_decode($value, true));
        }
        return collect([]);
    }
    public function setMetaDataAttribute($value)
    {
        if ($value) {
            $this->attributes['meta_data'] = json_encode($value);
        } else {
            $this->attributes['meta_data'] = json_encode([]);
        }
    }
}
