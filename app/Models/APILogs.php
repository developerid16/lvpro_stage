<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APILogs extends Model
{
    use HasFactory;
    protected $table = 'api_logs';
    protected $fillable = [
        'name', 'start_time', 'end_time', 'req_data', 'response_data', 'status', 'request_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'response_data' => 'array',
        'req_data' => 'array',

    ];
}
