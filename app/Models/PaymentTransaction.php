<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'mid',
        'transaction_id',
        'order_id',

        'transaction_type',
        'payment_mode',

        'request_amount',
        'request_ccy',

        'authorized_amount',
        'authorized_ccy',

        'response_code',
        'response_msg',

        'acquirer_transaction_id',
        'acquirer_response_code',
        'acquirer_response_msg',

        'request_timestamp',
        'acquirer_created_timestamp',
        'created_timestamp',

        'status',
        'signature',
        'raw_response',
        'user_id',
        'receipt_no',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'request_timestamp' => 'datetime',
        'acquirer_created_timestamp' => 'datetime',
        'created_timestamp' => 'datetime',
    ];
}
