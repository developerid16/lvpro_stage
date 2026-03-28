<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'status',
        'club_location_id',
        'added_by',

        'active_department_id',
        'active_club_location_id',
        'active_role_id'
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

