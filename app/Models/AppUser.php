<?php

namespace App\Models;

use App\Models\DeviceToken;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AppUser extends Authenticatable
{
    use HasFactory, HasApiTokens;
    protected $fillable = [
        'name', 'email', 'password', 'country_code', 'phone_number', 'gender', 'date_of_birth', 'my_code', 'referral_code', 'sms_subscription', 'email_subscription', 'whatsapp_subscription', 'unique_id', 
        'expiry_date', 'user_type', 'verified_at', 'otp', 'available_key', 'company_id', 'status', 'c_code', 'c_name', 'aircrew_unique', 'noti_count', 'push_system_noti', 'push_promotion_noti', 'push_other_noti',
         'sms_noti', 'email_noti', 'blacklist_reason','last_otp_time', 'password_reason', 'last_login','customer_id','password_reset',
         'is_vip','member_category','member_id','membership_type_code','modified_date_time','nric','membership_status','token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'otp'

    ];
    protected $casts = [
        'verified_at' => 'datetime',
        'last_otp_time' => 'datetime',
        'last_login' => 'datetime',
        'expiry_date' => 'date',
        'date_of_birth' => 'date',
    ];
    public function myTire(): HasOne
    {
        return $this->hasOne(UserTier::class, 'user_id')->where('status', "Active");
    }
    public function referral(): HasOne
    {
        return $this->hasOne(UserReferral::class, 'referral_to');
    }
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class, 'user_id');
    }
   
    public function scopeAgedBetween($query, $start, $end = null)
    {
        if (is_null($end)) {
            $end = $start;
        }

        $now = $this->freshTimestamp();
        $start = $now->subYears($start);
        $end            = $this->freshTimestamp()->subYears($end)->addYear()->subDay(); // plus 1 year minus a day

        return $query->whereBetween('date_of_birth', [$end->format('Y-m-d'), $start->format('Y-m-d')]);
    }
}
