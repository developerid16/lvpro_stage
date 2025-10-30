<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InAppNoti extends Model
{
    use HasFactory;
    protected $fillable = [
        'title', 'content', 'type', 'user_id'
    ];
    protected $table = "inapp_noti_list";
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
        static::created(function (InAppNoti $item) {
            AppUser::find($item->user_id)->increment('noti_count', 1);
        });
    }
}
