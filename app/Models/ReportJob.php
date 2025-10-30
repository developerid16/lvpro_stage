<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportJob extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'udid', 'status','type','name'];
    protected $table = "report_jobs";
    /**
     * Get the user that owns the UserPurchasedReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
