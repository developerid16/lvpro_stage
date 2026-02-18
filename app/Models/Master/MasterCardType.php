<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCardType extends Model
{
    use HasFactory;
    protected $table    = 'master_card_types';
    protected $fillable = ['card_type', 'card_description'];
}
