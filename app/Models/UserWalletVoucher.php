<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class UserWalletVoucher extends Model
{
    protected $fillable = [
        'receipt_no',
        'user_id',
        'reward_id',
        'qty',
        'claimed_at',
        'status',
        'location_id',
        'location_type',
        'redeemed_at',
        'reward_status',
        'reward_voucher_id',
        'serial_no',
        'used_code',
        'suspend_voucher'
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public static function generateReceiptNo(): string
    {
        $datePart = now()->format('dmY'); // 01102025
        $randomPart = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5);

        return $datePart . $randomPart;
    }
    
}
