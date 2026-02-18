<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMembershipCode extends Model
{
    use HasFactory;
    protected $table    = 'master_membership_codes';
    protected $fillable = ['membershiptype_id', 'description'];
}
