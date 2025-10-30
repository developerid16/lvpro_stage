<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;
    protected $table = "emails_log";
    protected $fillable = [
        'email',
        'type',
        'status',

    ];
}
