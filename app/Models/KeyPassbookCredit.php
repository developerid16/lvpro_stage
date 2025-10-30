<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeyPassbookCredit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'no_of_key', 'remain_keys', 'earn_way', 'meta_data', 'expiry_date','app_reason'
    ];
    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    protected $table = "key_passbook_credit";



    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
        static::created(function (KeyPassbookCredit $item) {
             AppUser::find($item->user_id)->increment('available_key', $item->remain_keys);
        });
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id', 'id');
    }
}
