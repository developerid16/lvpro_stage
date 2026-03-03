<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'status',
        'club_location_id',
    ];
    public $table = 'departments';

    public function participatingMerchants()
    {
        return $this->hasMany(ParticipatingMerchant::class, 'department_id');
    }

    public function clubLocation()
    {
        return $this->belongsTo(ClubLocation::class, 'club_location_id');
    }
}

