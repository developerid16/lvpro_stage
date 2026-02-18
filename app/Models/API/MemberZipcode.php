<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberZipcode extends Model
{
    use HasFactory;
     protected $table    = 'member_zipcode';
    protected $fillable = ['token','ZipCode','json'];
}
