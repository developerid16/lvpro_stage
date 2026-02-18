<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMaritalStatus extends Model
{
    use HasFactory;
    protected $table    = 'master_marital_statuses';
    protected $fillable = ['label', 'string_value'];
}
