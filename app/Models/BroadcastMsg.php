<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BroadcastMsg extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "broadcast_msg";

    protected $fillable = [
        'title',
        'sms_content',
        'date_of_publish',
        'type',
        'email_subject',
        'email_content',
        'inapp_title',
        'inapp_content',
        'push_title',
        'push_subtitle',
        'status',
        'csv_file',
        'attachments'
    ];
    protected $casts = [
        'date_of_publish' => 'datetime',
    ];
}
