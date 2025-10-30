<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnreadCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'all_noti_id',
        'personal_id',
    ];
    protected $table = "read_notification";


    public function setPersonalIdAttribute($value)
    {
        $this->attributes['personal_id'] = implode(',', $value);
    }
    public function getPersonalIdAttribute()
    {
        return array_filter(explode(",", $this->attributes['personal_id']));
    }
    public function setAllNotiIdAttribute($value)
    {
        $this->attributes['all_noti_id'] = implode(',', $value);
    }
    public function getAllNotiIdAttribute()
    {
        return array_filter(explode(",", $this->attributes['all_noti_id']));
    }
    // protected function allNotiId(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value, $attributes) => (explode(",", array_filter($value))),
    //         set: fn (array $value) => (implode(",", $value)),

    //     );
    // }
}
