<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Admin\SalesController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundSale extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'system_time', 'loyalty', 'location', 'storage_location', 'ref', 'sku', 'sale_amount', 'quantity_purchased', 'batch_id', 'key_earn', 'pos', 'voucher_no', 'org_rec_no','user_id'];


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('created_at', 'desc');
        });
        static::created(function (RefundSale  $item) {
             // // get user and create tire 
            // $key = SalesController::refundSale($item, $item->sale_amount);
            // $item->key_earn = $key;
            $user = AppUser::where('unique_id', $item->loyalty)->first();

            // $item->user_id =  $user->id ?? '';
            // $item->save();
            if($user){
                $item->user_id =  $user->id ?? '';
                $item->update();
                
            }else{
                
                $item->delete();
            }
            // dd($item);
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
    public function user(): BelongsTo
    {

        return $this->belongsTo(AppUser::class, 'loyalty', 'unique_id');
    }
}
