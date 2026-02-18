<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberLatestTransaction extends Model
{
    use HasFactory;
    protected $table    = 'member_latest_transaction';
    protected $fillable = ['token','TransactionDate','json'];
}
