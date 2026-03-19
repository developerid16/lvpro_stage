<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    protected $table = 'notification_user';
    protected $fillable = ['user_id','notification_id','is_read','read_at'];
    public $timestamps = true;
}
