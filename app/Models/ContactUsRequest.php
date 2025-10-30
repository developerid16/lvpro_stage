<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactUsRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'category', 'subject', 'message', 'name', 'email', 'mobile', 
    ];
    protected static function boot()
    {
        parent::boot();

        // Order by t_order ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
    }
}
