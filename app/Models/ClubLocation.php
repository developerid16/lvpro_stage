<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubLocation extends Model
{
    protected $fillable = [
        'merchant_id',
        'name',
        'status',
        'code'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
