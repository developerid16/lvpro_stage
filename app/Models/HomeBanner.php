<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
  protected $fillable = [
    'title',
    'description',
    'image',
    'action_type',
    'action_value',
    'position',
    'status',
    'start_at',
    'end_at'
  ];
}
