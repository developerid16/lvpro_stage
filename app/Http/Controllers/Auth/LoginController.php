<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\OTPVerify;
use App\Models\UserAccessRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Str; // to customize the throttle key
use Illuminate\Foundation\Auth\ThrottlesLogins;  // include the trait
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    use ThrottlesLogins, AuthenticatesUsers; // use the trait
    protected $maxAttempts = 5;
    protected $decayMinutes = 2; // In minutes
    protected $warning = 3; // Where we should show the warning message (attempts count)


    public function login(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) { // checking form maximum login attempts
            return $this->prepareLockMessage($request); // to customize the  message, you can use the below-commented default method
            //return $this->sendLockoutResponse($request); // laravel own response
        }
        try {

            $user = User::where([['email', $request->email], ['status', 'Active']])->first();
            if ($user && !Hash::check($request->password, $user->password, [])) {                 // login failed
                $this->incrementLoginAttempts($request);        // increment the attempt count

                if ($this->hasTooManyLoginAttempts($request)) { // checking form maximum login attempts
                    return $this->prepareLockMessage($request);
                } else {
                    $attempts_count = $this->limiter()->attempts($this->throttleKey($request)); // number of attempts performed
                    $message = "The email or password you have entered is incorrect.";
                    if ($attempts_count >= $this->warning) {         // before locking adding a warning message. 'warning' variable will handle this
                        $remaining = $this->maxAttempts - $attempts_count;    // number of attempts left
                        $message .= "Your account will be blocked after " . ($remaining) . " failed attempts.";
                    }
                    return back()->with('email', $message);
                }
            } else if (!$user) {
                return back()->with('email', "The email or password you have entered is incorrect.");
            } else if ($user && Hash::check($request->password, $user->password, [])) {
                Auth::loginUsingId($user->id);
                $this->clearLoginAttempts($request); // for the successful login clearing the attempts count
                $otp = generateNumericOTP(4);
                $data['name'] =  $user->name;
                $data['otp'] =   $otp;
                Mail::to($user->email)->send(
                    new OTPVerify($data)
                );
                // Session::put('OTP_VERIFY', $otp);
                $min = now()->addMinutes(15);
                Cache::add('OTP_VERIFY_' . $user->id, $otp, $min);
                Cache::add('END_TIME' . $user->id, $min, $min);


                return redirect('/admin/otp-verification');
            }
            // $user = User::where('email', $request->email)->first();
            // if (!Hash::check($request->password, $user->password, [])) {
            //     throw new \Exception('Error in Login');
            // }



        } catch (\Exception $e) {
            throw $e;
            // return response()->json([
            //     'status'        => false,
            //     'message'       => "something went wrong."
            // ], 422);
            return back()->with('email', "something went wrong.");
        }
    }

    /**
     * To specify the login username to be used.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Convert seconds to minute:seconds.
     */
    public function ToMinutes($seconds = 0)
    {
        $minutes = 0;
        if ($seconds > 0) {
            return gmdate("i:s", $seconds);
            //$minutes = intval($seconds/60);
        }
        return $minutes;
    }

    // To customize the lock message
    public function prepareLockMessage($request)
    {
        try {
            $this->fireLockoutEvent($request);          // checking the locking event

            $message = "Your account has been blocked. Please contact administrator.";
            User::where('email', $request->email)->update([
                'status' => 'lockout',
            ]);
            return back()->with('email', $message);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Customizing throttle key, use if needed
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input($this->username())) . '|' . $request->ip());
    }
    protected function showLoginForm()
    {
        return view('auth.login');
    }
    protected function userRightsForm()
    {
        return view('auth.user-rights-form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email',
            'description' => 'nullable|string|max:500',
        ]);

        UserAccessRequest::create($request->only('name', 'email', 'description'));

        return back()->with('success', 'Request submitted successfully. Admin will contact you.');
    }

   
}
