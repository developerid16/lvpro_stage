<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterInterestGroup extends Model
{
    use HasFactory;
    protected $table    = 'master_interest_groups';
    protected $fillable = [
        'interest_group_main_id',
        'interest_group_main_name',
        'interest_group_id',
        'interest_group_name',
        'ig_type',
    ];
}
