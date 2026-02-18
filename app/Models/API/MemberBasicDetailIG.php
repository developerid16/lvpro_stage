<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberBasicDetailIG extends Model
{
    use HasFactory;
    protected $table    = 'member_basic_detail_ig';
    protected $fillable = ['token','ExpiryDate','InterestGroupMainName','InterestGroupName','json'];
}
