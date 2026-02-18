<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberBasicDetailsModified extends Model
{
    use HasFactory;
    protected $table    = 'member_basic_details_modified';
    protected $fillable = ['token','BirthMonth','BirthYear','MemberCategory','MembershipTypeCode','SafraMembershipExpiry','json'];
}
