<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id','voucher_id','user_id','location_id','qty','price','location_type'];

    protected $hidden = ['created_at','updated_at'];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Reward::class, 'voucher_id');
    }

    public function wallet()
    {
        return $this->belongsTo(UserWalletVoucher::class, 'wallet_id');
    }
}
