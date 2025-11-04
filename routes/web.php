<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\FAQCategoryController;
use App\Http\Controllers\Admin\FAQController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Admin\ContactUsRequest;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\OTPVerifyController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\DashboardPopupController;
use App\Http\Controllers\Admin\AppUserController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\AboutAppSliderController;
use App\Http\Controllers\Admin\PartnerCompanyController;
use App\Http\Controllers\Admin\APILogsController;
use App\Http\Controllers\Admin\RewardRedemptionController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CampaignVoucherGroupController;
use App\Models\AppUser;
use App\Models\UserTier;
use App\Models\KeyPassbookCredit;
use App\Models\KeyPassbookDebit;
use App\Models\Sale;
use App\Models\RefundSale;



use Illuminate\Http\Request;
use App\Http\Controllers\Admin\EmailLogController;
use App\Mail\BroadcastEmail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();


//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('admin/otp-verification', [OTPVerifyController::class, 'index'])->name('otp.index');
    Route::post('admin/otp-verification', [OTPVerifyController::class, 'verify'])->name('otp.verify');
    Route::get('admin/otp-resend', [OTPVerifyController::class, 'resend'])->name('otp.resend');
});
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->middleware(['web', 'auth', 'OTPVerify'])->name('root');
Route::prefix('/admin')->name('admin.')->middleware(['web', 'auth', 'OTPVerify'])->group(function () {


    Route::get('/send-sms', [App\Http\Controllers\HomeController::class, 'emailSend']);

    Route::get('roles/datatable', [RoleController::class, 'datatable']);
    Route::resource('roles', RoleController::class);


    Route::get('reward-redemption/datatable', [RewardRedemptionController::class, 'datatable']);
    Route::post('reward-redemption-delete', [RewardRedemptionController::class, 'deleteReward']);
    Route::post('reward-redemption-change-date', [RewardRedemptionController::class, 'changeDateReward']);
    Route::resource('redemption-reward', RewardRedemptionController::class);

    Route::get('reward-redemption-pos/datatable', [RewardRedemptionController::class, 'datatablePOS']);
    Route::get('reward-redemption-pos', [RewardRedemptionController::class, 'posIndex']);
    Route::resource('redemption-reward-pos', RewardRedemptionController::class);

    Route::get('app-user/datatable', [AppUserController::class, 'datatable']);
    Route::resource('app-user', AppUserController::class);
    Route::get('app-user-edit/{id}', [AppUserController::class, 'editUser'])->name('app-user-edit');
    Route::get('app-user-transactions/{id}', [AppUserController::class, 'userTransactions'])->name('app-user-transactions');
    Route::post('keys-credit-debit', [AppUserController::class, 'adminKeyDebitCredit']);



    Route::get('user/datatable', [UserController::class, 'datatable']);
    Route::resource('user', UserController::class);

    Route::get('contact-us/datatable', [ContactUsRequest::class, 'datatable']);
    Route::get('contact-us', [ContactUsRequest::class, 'index']);
    Route::get('contact-us/{id}/show', [ContactUsRequest::class, 'show']);

    Route::get('sales/datatable', [SalesController::class, 'datatable']);
    Route::get('sales/retiveTransaction', [SalesController::class, 'retiveTransaction']);
    Route::post('sales/old-sales', [SalesController::class, 'oldSales']);

    // Route::resource('sales', SalesController::class);
    Route::get('apilogs/datatable', [APILogsController::class, 'datatable']);
    Route::resource('apilogs', APILogsController::class);

    Route::get('apilogs-sorting/datatable', [APILogsController::class, 'datatableTriggerd']);
    Route::get('apilogs-sorting', [APILogsController::class, 'indexTriggerd']);

    Route::get('voucherlogs/datatable', [APILogsController::class, 'datatableVoucherLogs']);
    Route::get('voucherlogs', [APILogsController::class, 'indexVoucher']);

    Route::get('partner-company/datatable', [PartnerCompanyController::class, 'datatable']);
    Route::get('partner-company/{id}/locations', [PartnerCompanyController::class, 'locationsIndex']);
    Route::resource('partner-company', PartnerCompanyController::class);

    Route::get('broadcast/datatable', [BroadcastController::class, 'datatable']);
    Route::post('broadcast/testing-template', [BroadcastController::class, 'broadcastTestingTemplate']);
    Route::resource('broadcast', BroadcastController::class);

    Route::get('locations/datatable', [LocationController::class, 'datatable']);
    Route::resource('locations', LocationController::class);

    Route::get('faq/datatable', [FAQController::class, 'datatable']);
    Route::post('faq/up-down', [FAQController::class, 'upDownFaq']);
    Route::resource('faq', FAQController::class);
    Route::get('faq-category/datatable', [FAQCategoryController::class, 'datatable']);
    Route::post('faq-category/up-down', [FAQCategoryController::class, 'upDownCategory']);
    Route::resource('faq-category', FAQCategoryController::class);
    Route::get('reward/datatable', [RewardController::class, 'datatable']);
    Route::resource('reward', RewardController::class);

    Route::get('automated-reward', [RewardController::class, 'indexAutomatedReward']);
    Route::post('automated-reward-update', [RewardController::class, 'updateAutomatedReward']);


    Route::get('campaign-voucher-group/datatable', [CampaignVoucherGroupController::class, 'datatable']);
    Route::get('campaign-voucher-assign/{id}', [CampaignVoucherGroupController::class, 'assignIndex']);
    Route::post('campaign-voucher-assign/{id}', [CampaignVoucherGroupController::class, 'assignStore']);
    Route::resource('campaign-voucher-group', CampaignVoucherGroupController::class);


    Route::get('redeem-voucher', [CampaignVoucherGroupController::class, 'redeemVoucher']);
    Route::post('redeem-voucher', [CampaignVoucherGroupController::class, 'redeemVoucherVerify']);

    Route::get('slider/datatable', [SliderController::class, 'datatable']);
    Route::resource('slider', SliderController::class);

    Route::get('dashboardpopup/datatable', [DashboardPopupController::class, 'datatable']);
    Route::resource('dashboardpopup', DashboardPopupController::class);

    Route::get('about-app-banner/datatable', [AboutAppSliderController::class, 'datatable']);
    Route::resource('about-app-banner', AboutAppSliderController::class);

    Route::get('content-management', [ContentManagementController::class, 'index']);
    Route::post('content-management/save', [ContentManagementController::class, 'update'])->name('content-management.store');


    Route::get('referral-rate', [ContentManagementController::class, 'referralRateIndex']);
    Route::post('referral-rate/save', [ContentManagementController::class, 'referralRateUpdate'])->name('referral-rate.store');

    Route::get('notification-setting', [ContentManagementController::class, 'notificationSettings']);
    Route::post('notification-setting/save', [ContentManagementController::class, 'notificationSettingsUpdate'])->name('notification-setting.store');

    Route::get('app-content-management', [ContentManagementController::class, 'appIndex']);
    Route::post('app-content-management/save', [ContentManagementController::class, 'appUpdate'])->name('app-content-management.store');

    Route::get('website-management', [ContentManagementController::class, 'applicationManagement']);
    Route::post('website-management/save', [ContentManagementController::class, 'applicationManagementSave']);

    Route::get('learn-more-page', [ContentManagementController::class, 'learnIndex']);
    Route::post('learn-more-page', [ContentManagementController::class, 'learnUpdate'])->name('learn.store');
    Route::get('email-log', [EmailLogController::class, 'index']);
    Route::post('email-log', [EmailLogController::class, 'store']);
    Route::get('email-log/datatable', [EmailLogController::class, 'datatable']);
    Route::get('tiers', [TierController::class, 'index']);
    Route::post('tiers/update', [TierController::class, 'update'])->name('tiers.update');
    Route::post('tiers-milestone/save', [TierController::class, 'milestoneSave'])->name('tiers.milestone.save');
    Route::get('report/customer', [ReportController::class, 'customerIndex']);
    Route::get('report/sales', [ReportController::class, 'sales']);
    Route::get('report/sales-download', [ReportController::class, 'salesReportDownload']);
    Route::get('report/customer-download', [ReportController::class, 'customerReportDownload']);
    Route::get('report-queue', [ReportController::class, 'reportQueueIndex']);
    Route::get('report-queue/datatable', [ReportController::class, 'reportQueueDatatable']);


    Route::get('report/reward', [ReportController::class, 'userPurchasedRewardReport']);

    Route::post('products/search', [ReportController::class, 'productSearch']);
    Route::post('image-upload-editor', [HomeController::class, 'editorImage']);

    Route::get('qr-setting', [ContentManagementController::class, 'qrSettings']);
    Route::post('qr-setting/save', [ContentManagementController::class, 'qrSettingsUpdate'])->name('qr-setting.store');

    Route::get('cms-setting', [ContentManagementController::class, 'cmsSettings']);
    Route::post('cms-setting/save', [ContentManagementController::class, 'cmsSettingsUpdate'])->name('cms-setting.store');

    Route::post('user/search', [ReportController::class, 'userSearch']);


    Route::get('download-report-file', function () {
        $pathToFile =  public_path('report/customer-report.xlsx');
        $name = 'customer-' . rand(100, 99999) . '.xlsx';

        return response()->download($pathToFile, $name);
    });
    Route::get('download-democsv', function () {
        $pathToFile =  public_path('demo-file.csv');
        $name = 'demo' . rand(100, 99999) . '.csv';
        return response()->download($pathToFile, $name);
    });
    Route::get('download-democsv-user', function () {
        $pathToFile =  public_path('demo-file-brodcast.csv');
        $name = 'demo-file-brodcast' . rand(100, 99999) . '.csv';
        return response()->download($pathToFile, $name);
    });
    Route::get('download-demo-campaign-voucher-assign', function () {
        $pathToFile =  public_path('demo-campaign-voucher-assign.csv');
        $name = 'demo-campaign-voucher-assign' . rand(100, 99999) . '.csv';
        return response()->download($pathToFile, $name);
    });
    Route::get('email-ecb', function (Request $request) {
        sendNotification("Sub Title", "Title", '66b438d5-0cc5-4b4a-84a0-a97b0b954754');

        dd("Push Noti");

        $date = Carbon\Carbon::createFromFormat('YmdHis', "20240629132602");
        $today = Carbon\Carbon::now();
        if ($date->gt($today)) {
            dd("GT");
        } else {

            dd("LT");
        }

        $noTranscationThisYear = [];
        $transcationThisYear = [];

        $activeUser = UserTier::where([['status', 'Active'], ['amount_spend', '>', '1']])->get();

        foreach ($activeUser as  $value) {
            $sales = Sale::whereDate('date', '>=', '2024-09-01')->where('user_id', $value->user_id)->groupBy('ref')
                ->selectRaw('*,sum(sale_amount) as totalsale')
                ->get();
            $totalSpend = 0;
            foreach ($sales as $key => $s) {
                $totalSpend +=  floor((float)$s->totalsale);
            }
            $refundsales = RefundSale::whereDate('date', '>=', '2024-09-01')->where('user_id', $value->user_id)->groupBy('ref')
                ->selectRaw('*,sum(sale_amount) as totalsale')
                ->get();
            foreach ($refundsales as $key => $s) {
                $totalSpend -=  floor((float)$s->totalsale);
            }
            // dump($value->user_id . " TOTAl SPE $value->amount_spend ==== $totalSpend" );


            if ($value->amount_spend ==  $totalSpend) {
                // Nothing to do here this user not effect
                //    dump("User is not effected " .  $value->user_id);
            } else {
                // this user effect need to reset 
                if ($totalSpend === 0) {


                    // dump("User is effected " .  $value->user_id . " TOTAl SPE $value->amount_spend ==== $totalSpend" );
                    $noTranscationThisYear[] = $value->user_id;
                } else {
                    $transcationThisYear[] = $value->user_id;
                    // dump("User is effected " .  $value->user_id . " TOTAl SPE $value->amount_spend ==== $totalSpend" );


                }
                //TODO: here we need to reset his tire
            }
        }


        // Now find the user dont spent anything but still get keys and they use that one or not 
        // TODO Done
        // $spentUser  = [];
        // $nospentUser  = [];

        // foreach ($noTranscationThisYear as $user) {
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();
        //     $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
        //     if (count($debitskey) > 0) {
        //         $spentUser[] =  $user;
        //     } else {
        //         $nospentUser[] =  $user;
        //     }
        // }

        // First remove the key from user that dont use them till now. so its easy 


        // foreach($nospentUser as $user){
        //     // Reset milestone bar and delete key and also remove from available key 00
        //     $ak = AppUser::find($user)->available_key;
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->sum('remain_keys');
        //     $lk = $ak - $kpc;
        //     if($lk >= 0){
        //         dump("$user User Still Remaining $lk total past $ak total earn $kpc");
        //     }else{
        //         dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");

        //     }
        // }
        // foreach($spentUser as $user){
        //     // Reset milestone bar and delete key and also remove from available key 
        //     $ak = AppUser::find($user)->available_key;
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->sum('no_of_key');
        //     $lk = $ak - $kpc;
        //     if($lk >= 0){
        //         dump("$user User Still Remaining $lk total past $ak total earn $kpc");
        //     }else{
        //         dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");

        //     }
        // }
        // TODO END
        // dd($spentUser,$nospentUser);


        // now check for the user have transcationThisYear and find the thigs 
        $nospentUser = [];
        $spentUser = [];
        foreach ($transcationThisYear as $user) {
            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();
            $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
            if (count($debitskey) > 0) {
                $spentUser[] =  $user;
            } else {
                $nospentUser[] =  $user;
            }
        }
        //dd($spentUser);


        foreach ($spentUser as $user) {
            // Reset milestone bar and delete key and also remove from available key 


            $ak = AppUser::find($user)->available_key;
            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();

            $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
            $lkpc = $kpc->sum('remain_keys');

            if (count($debitskey) > 0) {

                // first find the key we give user as un-wanted


                $sales = Sale::whereDate('date', '<', '2024-09-01')->whereDate('created_at', '>=', "2024-09-01")->where([['user_id', $user], ['batch_id', '!=', '31'], ['batch_id', '!=', '30']])->groupBy('ref')
                    ->selectRaw('*,sum(key_earn) as totalsale')
                    ->get();
                $totalSpend = 0;
                foreach ($sales as $key => $s) {
                    $totalSpend +=  floor((float)$s->totalsale);
                }
                $refundsales = RefundSale::whereDate('date', '<', '2024-09-01')->whereDate('created_at', '>=', "2024-09-01")->where([['user_id', $user], ['batch_id', '!=', '31'], ['batch_id', '!=', '30']])->groupBy('ref')
                    ->selectRaw('*,sum(key_earn) as totalsale')
                    ->get();
                foreach ($refundsales as $key => $s) {
                    $totalSpend -=  floor((float)$s->totalsale);
                }

                if ($ak - $totalSpend < 0) {

                    dump("call here $user total key used by "  . $debitskey->sum('key_use') . " available key " . $ak . " total-key earn user " . $totalSpend . " Remove key from available key" .  $ak - $totalSpend);
                }
                // think in morning 
                // $lk = $ak - $lkpc;
                // if ($lk >= 0) {
                //     dump("$user User Still Remaining $lk total past $ak total earn $kpc");
                // } else {
                //     dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");
                // }
            } else {
                // so here we delete the all the key that given to user and reset tire in next step we again provide the key to users and also remove the available key based on remonain keys 

                // We get remian kes in case of user refund becase user spend nothing here 
                $lkpc = $kpc->sum('remain_keys');

                $lk = $ak - $lkpc;
                if ($lk >= 0) {
                    dump("$user User Still Remaining $lk total past $ak total earn $lkpc");
                } else {
                    dump("$user User has nagative key Remaining $lk total past $ak total earn $lkpc");
                }
            }
        }
        // foreach ($nospentUser as $user) {
        //     // Reset milestone bar and delete key and also remove from available key 


        //     $ak = AppUser::find($user)->available_key;
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();

        //     $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
        //     $lkpc = $kpc->sum('remain_keys');

        //     if (count($debitskey) > 0) {
        //         dump("call here $user");
        //         // think in morning 
        //         // $lk = $ak - $lkpc;
        //         // if ($lk >= 0) {
        //         //     dump("$user User Still Remaining $lk total past $ak total earn $kpc");
        //         // } else {
        //         //     dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");
        //         // }
        //     }else{
        //         // so here we delete the all the key that given to user and reset tire in next step we again provide the key to users and also remove the available key based on remonain keys 

        //         // We get remian kes in case of user refund becase user spend nothing here 
        //         $lkpc = $kpc->sum('remain_keys');

        //         $lk = $ak - $lkpc;
        //         if ($lk >= 0) {
        //             dump("$user User Still Remaining $lk total past $ak total earn $lkpc");
        //         } else {
        //             dump("$user User has nagative key Remaining $lk total past $ak total earn $lkpc");
        //         }

        //     }

        // }
        dd("Done");


        // Lets end it here all things 

        dd("retunr");

        // return view('email.edm');

        $email = $request->email;

        if (!$email) {
            sendNotification("Sub Title", "Title", 'b45328d6-599b-4a93-a3dd-63341cc5d0bf');

            dd("Push Noti");
        }
        // \Mail::bcc([$email])->send(
        //     new BroadcastEmail("SUB", "asdasd")
        // );
        $provider = substr($email, strpos($email, '@') + 1);
        \Mail::send('email.edm', [
            "name" => "test",
            "unique_id" => "1234567",
            "provider" => $provider
        ], function ($message) use ($email) {

            $message->bcc($email, 'Dummy Name');
            $message->subject('The wait is over. Meet our new Shilla Access App!');
            $message->priority(3);
            $message->attach('https://shillauatcms.trinaxmind.com/attachment/Shilla_Access_APP_Testing_Checklist.pdf');
        });
        \Mail::send('email.broadcast', [
            "data" => "test",

        ], function ($message) use ($email) {

            $message->bcc($email, 'Dummy Name');
            $message->subject('broadcast');
            $message->priority(3);
            // $message->attach('https://shillauatcms.trinaxmind.com/attachment/Shilla_Access_APP_Testing_Checklist.pdf');

        });
        // \Mail::send('email.edmafter', [], function ($message) {

        //     $message->bcc('johncinag@mailinator.com', 'John Doe');
        //     $message->subject('Subject');
        //     $message->priority(3);
        // });
        dd("Mail Send");
        // Mail::send('email_view', $data, function ($m) use ($user) {
        //     $m->from("example@gmail.com", config('app.name', 'APP Name'));
        //     $m->to($user->email, $user->name)->subject('Email Subject!'); 
        // });
        return view('email.edmafter');
        $emails = AppUser::where('email_noti', 1)->pluck('email')->toArray();
        dd($emails);
        $chunks = $emails->chunk(99)->toArray();
        foreach ($chunks as $key => $emilChunk) {
            dd($emilChunk, $emilChunk[0]);
        }
    });
});
