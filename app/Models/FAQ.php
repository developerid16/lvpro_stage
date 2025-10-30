<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class FAQ extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "faqs";
    protected $fillable = [
        'answer',
        'question',
        'is_for',
        'status',
        'category_id',
        'faq_order'
    ];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('faq_order', 'desc');
        });
    }
   
}
