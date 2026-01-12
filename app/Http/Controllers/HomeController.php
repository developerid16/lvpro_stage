<?php

namespace App\Http\Controllers;

use App\Mail\RewardExpiredMail;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Reward;
use App\Models\AppUser;
use App\Models\RefundSale;
use App\Mail\AccountVerify;
use App\Mail\APHExpiry;
use App\Mail\BroadcastEmail;
use App\Mail\ForgotPassword;
use App\Mail\KeyExpiry;
use App\Models\BrandMapping;
use Illuminate\Http\Request;
use App\Mail\NewAdminRegister;
use App\Models\KeyPassbookDebit;
use App\Models\KeyPassbookCredit;
use App\Models\UserAccessRequest;
use Illuminate\Support\Facades\DB;
use App\Models\UserPurchasedReward;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
    use App\Services\SafraService;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function index(Request $request)
    {
        return redirect('/');
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }
    function editorImage(Request $request)
    {
        $post_data = $this->validate($request, [

            'file' => 'required|image',
        ]);

        $fileName = '';
        if ($request->hasFile('file')) {
            $imageName = time() . rand() . '.' . $request->file->extension();
            $request->file->move(public_path('images'), $imageName);
            $fileName  = $imageName;
        }
        return response()->json(['location' => $fileName]);
    }
    function emailSend()
    {

        $email = "sagar@gmail.com";
        $data['name'] =  'Sagar';
        $data['otp'] =  '0000';
        Mail::to($email)->send(
            new ForgotPassword($data)
        );

        Mail::to($email)->send(
            new AccountVerify($data)
        );
        Mail::to($email)->send(
            new BroadcastEmail("subject", '<p>Changes</p>',[])
        );
        $keyData['text'] = "You have  Keys that are expiring within  days";
        $keyData['balance'] = "Keys Balance: Keys";

        Mail::to($email)->send(
            new KeyExpiry($keyData)
        );
        $data['password'] = "asd";
        $data['name'] =  "asd";
        Mail::to($email)->send(
            new NewAdminRegister($data)
        );

        $keyData['text'] = "Your rewards are expiring in  days. Login to view your rewards now!";

        Mail::to($email)->send(
            new RewardExpiredMail($keyData)
        );
        $emailData['text'] = "Your account is expiring within  days. Please update your Airport Pass ID and expiry date on the app to retain your account and Keys earned.";

        Mail::to($email)->send(
            new APHExpiry($emailData)
        );
        dd("all email send");
    }

    public function root()
    {
            return view('index', );
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar = '/images/' . $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            return response()->json([
                'isSuccess' => true,
                'Message' => "User Details Updated successfully!"
            ], 200); // Status code here
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            return response()->json([
                'isSuccess' => true,
                'Message' => "Something went wrong!"
            ], 200); // Status code here
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }

      public function checkMember(SafraService $safra)
    {
        // $response = $safra->call('sfrControlMember/GetClubHouseList');

        // $response = $safra->call(
        //     'sfrControlMember/CheckIsAxMember',
        //     [
        //         ['Name' => 'NRIC', 'Value' => 'A100479032']
        //     ]
        // );
       
        // $response = $safra->call(
        //     'sfrControlCart/GetGlobalCartNo',
        //     [
        //         'NRIC' => 'A100479032',
        //     ],
        //     'request'
        // );
            
       
        // $response = $safra->call(
        //     'sfrControlMember/GetSafraMemberCheck',
        //     [
        //         'MemberID' => 'A100479032',
        //         'DOB'      => '1994-09-17T00:00:00',
        //     ],
        //     'request'
        // );
            
        // $response = $safra->call(
        //     'sfrControlMember/GetMemberCheckIn',
        //     [
        //         'SearchType'  => 1,
        //         'MemberId'    => 'A100479032',
        //         'MobilePhone' => '',
        //         'Email'       => '',
        //     ],
        //     'request' 
        // );

        $response = $safra->call(
            'sfrControlMember/GetBasicDetailInfoByModified',
            [
                'LastModifiedTime' => '1994-09-17T00:00:00',
                'Limit' => 1,
            ],
            'request'
        );

        // $response = $safra->getTwcInfo('1CC2143B-FB5B-43A8-8636-6275F50CF4C9');


        return response()->json($response->json());

    }
}
