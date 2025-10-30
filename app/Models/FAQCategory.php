<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FAQCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "faq_category";
    protected $fillable = [
        'name',
        'category_order',
        'is_for',
        'status',
    ];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('category_order', 'desc');
        });
    }
    public function scopeFAQ($query)
    {
        return $query->where('status', 'Active')->where(function ($q) {
            $q->where('is_for', 'Both')->orWhere('is_for', 'FAQ');
        });
    }
    public function scopeChatBot($query)
    {
        return $query->where('status', 'Active')->where(function ($q) {
            $q->where('is_for', 'Both')->orWhere('is_for', 'Chat Bot');
        });
    }
    /**
     * The faqs that belong to the FAQCategory
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faqs(): HasMany
    {
        return $this->HasMany(FAQ::class,'category_id','id')->where('status', 'Active');
    }
}
