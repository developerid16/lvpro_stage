<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];
    public $table = 'category';

    public function rewards()
    {
        return $this->hasMany(Reward::class, 'category_id');
    }
}

