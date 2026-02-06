<?php

use App\Http\Controllers\Admin\CsoPurchaseController;
use App\Http\Controllers\Admin\EvoucherController;
use App\Http\Controllers\Admin\RewardUpdateRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\OTPVerifyController;
use App\Http\Controllers\Admin\DashboardPopupController;
use App\Http\Controllers\Admin\AppUserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\APILogsController;
use App\Http\Controllers\Admin\BdayEvoucherController;
use App\Http\Controllers\Admin\RewardRedemptionController;
use App\Http\Controllers\Admin\ContentManagementController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CampaignVoucherGroupController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClubLocationController;
use App\Http\Controllers\Admin\CsoIssuanceController;
use App\Http\Controllers\Admin\CsoPhysicalController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HomeBannerController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ParticipatingMerchantController;
use App\Http\Controllers\Admin\ParticipatingMerchantLocationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PushVoucherController;
use App\Http\Controllers\Admin\TransactionHistoryController;
use App\Http\Controllers\Admin\UserRightsRequestController;
use App\Http\Controllers\Admin\VoucherListController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MicrosoftAuthController;
use Illuminate\Support\Facades\Artisan;

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
Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    return 'All cache, config, route & view cleared and rebuilt';
});

Route::any('/pending_reward_request', function () {
    return view('email.pending_reward_request');
});

Route::get('/sso-redirect', [MicrosoftAuthController::class, 'redirect']);// web.php
Route::get('/sso-callback', [MicrosoftAuthController::class, 'login']);// web.php


Route::get('/user-rights-form', [LoginController::class, 'userRightsForm']);
Route::post('/user-rights-form', [LoginController::class, 'store']);
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

    Route::get('/safra-check', [HomeController::class, 'checkMember']);

    Route::post('user-rights/approve', action: [UserRightsRequestController::class, 'approve']);
    Route::post('user-rights/{id}/reject', [UserRightsRequestController::class, 'reject']);
    Route::get('user-rights/datatable', [UserRightsRequestController::class, 'datatable']);
    Route::resource('user-rights', UserRightsRequestController::class);

    Route::post('reward-update-request/approve', action: [RewardUpdateRequestController::class, 'approve']);
    Route::post('reward-update-request/{id}/reject', [RewardUpdateRequestController::class, 'reject']);
    Route::get('reward-update-request/datatable', [RewardUpdateRequestController::class, 'datatable']);
    Route::resource('reward-update-request', RewardUpdateRequestController::class);

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

   
    // Route::resource('sales', SalesController::class);
    Route::get('apilogs/datatable', [APILogsController::class, 'datatable']);
    Route::resource('apilogs', APILogsController::class);

    Route::get('apilogs-sorting/datatable', [APILogsController::class, 'datatableTriggerd']);
    Route::get('apilogs-sorting', [APILogsController::class, 'indexTriggerd']);

    Route::get('voucherlogs/datatable', [APILogsController::class, 'datatableVoucherLogs']);
    Route::get('voucherlogs', [APILogsController::class, 'indexVoucher']);


    Route::get('locations/datatable', [LocationController::class, 'datatable']);
    Route::resource('locations', LocationController::class);

    
    Route::get('/reward/get-locations/{merchant_id}', [RewardController::class, 'getMerchantLocations']);
    Route::get('reward/datatable', [RewardController::class, 'datatable']);
    Route::get('/reward/get-participating-merchant-locations',[RewardController::class, 'getParticipatingMerchantLocations']);

    Route::resource('reward', RewardController::class);

    Route::get('automated-reward', [RewardController::class, 'indexAutomatedReward']);
    Route::post('automated-reward-update', [RewardController::class, 'updateAutomatedReward']);


    Route::get('campaign-voucher-group/datatable', [CampaignVoucherGroupController::class, 'datatable']);
    Route::get('campaign-voucher-assign/{id}', [CampaignVoucherGroupController::class, 'assignIndex']);
    Route::post('campaign-voucher-assign/{id}', [CampaignVoucherGroupController::class, 'assignStore']);
    Route::resource('campaign-voucher-group', CampaignVoucherGroupController::class);


    Route::get('redeem-voucher', [CampaignVoucherGroupController::class, 'redeemVoucher']);
    Route::post('redeem-voucher', [CampaignVoucherGroupController::class, 'redeemVoucherVerify']);

  

    Route::post('dashboardpopup/reorder', [DashboardPopupController::class, 'reorder']);
    Route::get('dashboardpopup/datatable', [DashboardPopupController::class, 'datatable']);
    Route::resource('dashboardpopup', DashboardPopupController::class);
    
   
    Route::post('announcements/reorder', [AnnouncementController::class, 'reorder']);
    Route::get('announcement/datatable', [AnnouncementController::class, 'datatable']);
    Route::resource('announcement', AnnouncementController::class);

 
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
 
    // inside admin route group OR top-level, depending on your app
    Route::get('tiers/datatable', [TierController::class, 'datatable'])->name('admin.tiers.datatable');
    Route::resource('tiers', TierController::class);
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
  

    //8-12-25
    
    Route::get('merchants/datatable', [MerchantController::class, 'datatable'])->name('admin.merchants.datatable');
    Route::resource('merchants', MerchantController::class);
    
    Route::get('merchant/{merchant}/club-location', [ClubLocationController::class, 'index'])->name('admin.club-location.index');
    Route::get('club-location/datatable', [ClubLocationController::class, 'datatable'])->name('admin.club-location.datatable');
    Route::resource('club-location', ClubLocationController::class);
    
    Route::get('participating-merchant/datatable', [ParticipatingMerchantController::class, 'datatable'])->name('admin.participating-merchant.datatable');
    Route::resource('participating-merchant', ParticipatingMerchantController::class);
    
    Route::get('participating-merchant/{merchant}/location', [ParticipatingMerchantLocationController::class, 'index'])->name('admin.location.index');
    Route::get('participating-merchant/{merchant}/location/create',[ParticipatingMerchantLocationController::class, 'create'])->name('admin.location.create');
    Route::get(
        'participating-merchant-location/datatable', [ParticipatingMerchantLocationController::class, 'datatable'] )->name('participating-merchant-location.datatable');
    Route::resource('participating-merchant-location', ParticipatingMerchantLocationController::class);


    Route::get('category/datatable', [CategoryController::class, 'datatable'])->name('admin.category.datatable');
    Route::resource('category', CategoryController::class);

    Route::get('departments/datatable', [DepartmentController::class, 'datatable'])->name('admin.department.datatable');
    Route::resource('departments', DepartmentController::class);

    Route::get('permissions/datatable', [PermissionController::class, 'datatable'])->name('admin.permission.datatable');
    Route::resource('permissions', PermissionController::class);


    Route::post('evoucher/push-parameter-voucher', [EvoucherController::class, 'pushParameterVoucher'])->name('pushParameterVoucher');
    Route::post('evoucher/push-member-voucher', [EvoucherController::class, 'pushMemberVoucher'])->name('pushMemberVoucher');
    Route::get('reward/get-dates/{id}', [EvoucherController::class, 'getDates']);
    Route::get('evoucher/datatable', [EvoucherController::class, 'datatable']);
    Route::resource('evoucher', EvoucherController::class);

    Route::get('birthday-voucher/datatable', [BdayEvoucherController::class, 'datatable']);
    Route::get('birthday-voucher/get-club-locations', [BdayEvoucherController::class, 'getLocations'])->name('get.club.locations');
    Route::resource('birthday-voucher', BdayEvoucherController::class);
    
    Route::get('push-voucher/datatable', action: [PushVoucherController::class, 'datatable']);
    Route::resource('push-voucher', PushVoucherController::class);
    
    Route::post('/purchase/complete', [CsoPurchaseController::class, 'complete']);
    Route::post('/purchase/cancel', [CsoPurchaseController::class, 'cancel']);
    Route::post('/checkout', [CsoPurchaseController::class, 'checkout']);
    Route::post('/get-member-details', [CsoPurchaseController::class, 'getMemberDetails']);
    Route::resource('cso-purchase', CsoPurchaseController::class);

    
    Route::post('cso-physical/issue', [CsoPhysicalController::class, 'issue']);
    Route::get('cso-physical/view/{id}', [CsoPhysicalController::class, 'view']);
    Route::get('cso-physical/datatable', action: [CsoPhysicalController::class, 'datatable']);
    Route::resource('cso-physical', CsoPhysicalController::class);    

    Route::get('cso-issuance/datatable', action: [CsoIssuanceController::class, 'datatable']);
    Route::resource('cso-issuance', CsoIssuanceController::class);    

    Route::get('notification/datatable', [NotificationController::class, 'datatable'])->name('notification.datatable');
    Route::resource('notification', NotificationController::class);

    Route::get('home-banner/datatable', [HomeBannerController::class, 'datatable'])->name('home-banner.datatable');
    Route::resource('home-banner', HomeBannerController::class);

    Route::get('voucher-list/datatable', [VoucherListController::class, 'datatable'])->name('voucher-list.datatable');
    Route::get('voucher-list/{id}', [VoucherListController::class,'show']);
    Route::delete('voucher-list/{id}', [VoucherListController::class,'destroy']);
    Route::post('voucher-list/suspend', [VoucherListController::class,'toggleSuspend'])->name('voucher.suspend');
    Route::resource('voucher-list', VoucherListController::class);

    Route::get('transaction-history/datatable', [TransactionHistoryController::class, 'datatable'])->name('transaction-history.datatable');
    Route::resource('transaction-history', TransactionHistoryController::class);

});

