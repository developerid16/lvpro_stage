<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AddsAddedBy
{
    protected static function bootAddsAddedBy()
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->added_by)) {
                $model->added_by = Auth::id();
            }
        });
    }
}
