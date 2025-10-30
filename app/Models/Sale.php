<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Admin\SalesController;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'system_time', 'loyalty', 'location', 'storage_location', 'ref', 'sku', 'sale_amount', 'quantity_purchased', 'batch_id', 'key_earn', 'pos', 'brand_code', 'voucher_no','limit_reach','user_id'];
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
        static::created(function (Sale $item) {

             // get user and create tire 
            Log::info($item->sale_amount . " AMT");

            // SalesController::updateMileStone($item, $item->sale_amount);
            $brandData = BrandMapping::where('sku', $item->sku)->first();
            if ($brandData) {
                $item->brand_code = $brandData->brand_code;
            }
            $user = AppUser::where('unique_id', $item->loyalty)->first();
            if($user){
                $item->user_id =  $user->id ?? '';
                $item->update();
                
            }else{
                
                $item->delete();
            }

        });
    }

    protected $casts = [
        'date' => 'date',

    ];
    public function getSystemTimeAttribute($value)
    {
        if ($value) {
            try {
                //code...
                return  Carbon::createFromFormat('His', $value)->format('H:i:s');
            } catch (\Throwable $th) {
                //throw $th;
                return null;
            }
        }
    }
    /**
     * Get the brandData that owns the Sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandMapping::class, 'sku', 'sku');
    }
}

// [
//     {
//         m_name: "",
//         m_id: "",
//         m_amount: "",
//         m_no_of_keys: "",
//         m_type: '',
//         m_reward_id: "",
//         m_reach_date: ''
//     }
// ]