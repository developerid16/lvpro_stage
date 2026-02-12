<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppContent extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'file_path', 'content'];

}
