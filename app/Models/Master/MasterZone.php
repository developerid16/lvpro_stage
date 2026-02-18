<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterZone extends Model
{
    use HasFactory;
    protected $table    = 'master_zones';
    protected $fillable = ['zone_name', 'zone_code'];
}
