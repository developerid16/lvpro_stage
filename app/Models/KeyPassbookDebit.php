<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class KeyPassbookDebit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'purchase_id', 'credit_id',
        'key_use', 'type','meta_data','app_reason'
    ];
    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    protected $table = "key_passbook_debit";



    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }
}
