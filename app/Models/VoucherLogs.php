<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherLogs extends Model
{
    use HasFactory;
    protected $table = 'voucher_logs';
    protected $fillable = [
        'voucher_no', 'from_status', 'to_status', 'from_where', 'remark'
    ];

    protected $casts = [
         
    ];
}
