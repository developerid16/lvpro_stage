<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubLocation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'merchant_id',
        'name',
        'status',
        'code',
        'added_by',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
