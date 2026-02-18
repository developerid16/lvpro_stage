<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGender extends Model
{
    use HasFactory;
     protected $table    = 'master_genders';
    protected $fillable = ['label', 'string_value'];
}
