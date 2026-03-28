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
        'active_department_id',
        'active_club_location_id',
        'active_role_id'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
