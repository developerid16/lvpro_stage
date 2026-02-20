<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'unique_code',
        'used_code',
        'suspend_voucher',
        'push_voucher_member_id'

        
    ];
    protected $casts = [
        'claimed_at'   => 'datetime',
        'redeemed_at'  => 'datetime',
        'created_at'   => 'datetime',
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
    
     public static function generateUniqueVoucherCode(): string
    {
        return strtoupper(Str::random(4) . '-' . Str::random(4));
    }

     public static function generateSerialNo(string $code, int $index): string
    {
        return $code . '-' . str_pad($index, 4, '0', STR_PAD_LEFT);
    }
}
