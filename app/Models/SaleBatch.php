<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleBatch extends Model
{
    use HasFactory;
    protected $fillable = ['file_name', 'status', 'upload_by'];
    protected $table = "sale_batch";
    /**
     * Get the user that owns the UserPurchasedReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upload_by', 'id');
    }
}
