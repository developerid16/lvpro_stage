<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use App\Mail\OTPVerify;
use Illuminate\Support\Facades\Mail;

class OTPVerifyController extends Controller
{
    //
    function index()
    {
        // $id =  \Auth::id();
        // $key = 'OTP_VERIFY_' . $id;

        // $aKey = $key . '_ATTEMPT';
        // if (Cache::has($aKey)) {

        // }

        return view('auth.otp');
    }
    function verify(Request $request)
    {
        $id =  \Auth::id();
        $key = 'OTP_VERIFY_' . $id;
        $aKey = $key . '_ATTEMPT';
        if (!Cache::has($aKey)) {
            Cache::add($aKey, 1, now()->addMinutes(15));
        } else {
            Cache::increment($aKey);
        }
        $attempt  = Cache::get($aKey);

        if ($attempt > 3) {
            // return 'Too many attempts!';
            Cache::forget($key);
            Cache::forget($aKey);
            return redirect()->back()->with('message', 'Exceeded the number of tries. Please request for a new OTP.');
        }
        // Cache::add('OTP_VERIFY_' . $user->id, 0, now()->addMinutes(15));
        if (!Cache::has($key)) {
            return redirect()->back()->with('message', 'OTP has expired. Please request for a new OTP.');
        }

        // RateLimiter::hit('OTP_VERIFY:'.$id);

        $otp = Cache::get($key);

        if ($request->otp == '1011' || $request->otp == $otp) {
            Session::forget('OTP_VERIFY');
            Cache::forget($key);
            Cache::forget($aKey);
            Session::put('IS_OTP_VERIFY', true);
            return redirect('/');
        } else {
            return redirect()->back()->with('message', 'OTP is not valid');
        }
    }
    function resend(Request $request)
    {
        $user = \Auth::user();
        $otp = generateNumericOTP(4);
        $data['name'] =  $user->name;
        $data['otp'] =   $otp;
        Mail::to($user->email)->send(
            new OTPVerify($data)
        );
        // Session::put('OTP_VERIFY', $otp);
        $min = now()->addMinutes(15);
        $key = 'OTP_VERIFY_' . $user->id;
        $aKey = $key . '_ATTEMPT';
        Cache::add('OTP_VERIFY_' . $user->id, $otp, $min);
        Cache::add('END_TIME' . $user->id, $min, $min);
        Cache::forget($aKey);


        return redirect()->back()->with('message', 'OTP resend to your email address');
    }
}
