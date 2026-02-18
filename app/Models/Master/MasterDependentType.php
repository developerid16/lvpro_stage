<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDependentType extends Model
{
    use HasFactory;
    protected $table    = 'master_dependent_types';
    protected $fillable = ['label', 'string_value'];
}
