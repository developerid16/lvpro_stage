<?php

namespace App\Providers;

use App\Observers\ActivityObserver;
use App\Http\Controllers\Admin\SalesController;
use App\Models\Announcement;
use App\Models\API\MemberBasicDetailIG;
use App\Models\API\MemberBasicDetailsModified;
use App\Models\API\MemberLatestTransaction;
use App\Models\API\MemberZipcode;
use App\Models\APILogs;
use App\Models\AppContent;
use App\Models\AppUser;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\ContactUsRequest;
use App\Models\ContentManagement;
use App\Models\CustomLocation;
use App\Models\DashboardPopup;
use App\Models\Department;
use App\Models\Fabs;
use App\Models\Location;
use App\Models\Master\MasterCardType;
use App\Models\Master\MasterDependentType;
use App\Models\Master\MasterGender;
use App\Models\Master\MasterInterestGroup;
use App\Models\Master\MasterMaritalStatus;
use App\Models\Master\MasterMembershipCode;
use App\Models\Master\MasterZone;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\ParticipatingMerchantLocation;
use App\Models\PaymentTransaction;
use App\Models\Purchase;
use App\Models\PushVoucherLog;
use App\Models\PushVoucherMember;
use App\Models\Reward;
use App\Models\RewardDates;
use App\Models\RewardLocation;
use App\Models\RewardLocationUpdate;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardTierRate;
use App\Models\RewardUpdateRequest;
use App\Models\RewardVoucher;
use App\Models\RoleHasPermission;
use App\Models\Sale;
use App\Models\SaleBatch;
use App\Models\Tier;
use App\Models\TierInterestGroup;
use App\Models\TierMemberType;
use App\Models\TransactionHistory;
use App\Models\User;
use App\Models\UserPurchasedReward;
use App\Models\UserTier;
use App\Models\UserWalletVoucher;
use App\Models\VoucherLog;
use Faker\Provider\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;




class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Force HTTPS in non-local environments
        // if (env('APP_ENV') != 'local') {
            // URL::forceScheme('https');
        // }

        //All Module Logs
        Reward::observe(ActivityObserver::class);
        User::observe(ActivityObserver::class);
        Announcement::observe(ActivityObserver::class);
        APILogs::observe(ActivityObserver::class);
        AppContent::observe(ActivityObserver::class);
        AppUser::observe(ActivityObserver::class);
        Category::observe(ActivityObserver::class);
        ClubLocation::observe(ActivityObserver::class);
        ContactUsRequest::observe(ActivityObserver::class);
        ContentManagement::observe(ActivityObserver::class);
        CustomLocation::observe(ActivityObserver::class);
        DashboardPopup::observe(ActivityObserver::class);
        Department::observe(ActivityObserver::class);
        Fabs::observe(ActivityObserver::class);
        Notification::observe(ActivityObserver::class);
        Location::observe(ActivityObserver::class);
        Merchant::observe(ActivityObserver::class);
        ParticipatingLocations::observe(ActivityObserver::class);
        ParticipatingMerchant::observe(ActivityObserver::class);
        ParticipatingMerchantLocation::observe(ActivityObserver::class);
        PushVoucherLog::observe(ActivityObserver::class);
        PaymentTransaction::observe(ActivityObserver::class);
        Purchase::observe(ActivityObserver::class);
        PushVoucherMember::observe(ActivityObserver::class);
        RewardDates::observe(ActivityObserver::class);
        RewardLocation::observe(ActivityObserver::class);
        RewardLocationUpdate::observe(ActivityObserver::class);
        RewardParticipatingMerchantLocationUpdate::observe(ActivityObserver::class);
        RewardTierRate::observe(ActivityObserver::class);
        RewardUpdateRequest::observe(ActivityObserver::class);
        RewardVoucher::observe(ActivityObserver::class);
        RoleHasPermission::observe(ActivityObserver::class);
        Tier::observe(ActivityObserver::class);
        TierInterestGroup::observe(ActivityObserver::class);
        TierMemberType::observe(ActivityObserver::class);
        TransactionHistory::observe(ActivityObserver::class);
        User::observe(ActivityObserver::class);
        UserPurchasedReward::observe(ActivityObserver::class);
        UserTier::observe(ActivityObserver::class);
        UserWalletVoucher::observe(ActivityObserver::class);
        VoucherLog::observe(ActivityObserver::class);
        //Master api model
        MasterZone::observe(ActivityObserver::class);
        MasterMembershipCode::observe(ActivityObserver::class);
        MasterMaritalStatus::observe(ActivityObserver::class);
        MasterInterestGroup::observe(ActivityObserver::class);
        MasterGender::observe(ActivityObserver::class);
        MasterDependentType::observe(ActivityObserver::class);
        MasterCardType::observe(ActivityObserver::class);
        MemberZipcode::observe(ActivityObserver::class);
        MemberLatestTransaction::observe(ActivityObserver::class);
        MemberBasicDetailsModified::observe(ActivityObserver::class);
        MemberBasicDetailIG::observe(ActivityObserver::class);
        


        Paginator::useBootstrap();

        // URL::forceScheme('https');   
        Schema::defaultStringLength(191);
        Queue::after(function (JobProcessed $event) {

            $payload = $event->job->payload();

            if ($payload['displayName'] === "Maatwebsite\Excel\Jobs\AfterImportJob") {

                try {
                    // //code...
                    // $sb = SaleBatch::where('status', 'In Process')->orderBy('id', 'desc')->first();
                    // if ($sb) {
                    //     $sales = Sale::where('batch_id', $sb->id)->selectRaw('*, sum(sale_amount) as tot_sale_amount')->groupBy('ref')->get();
                    //     foreach ($sales as $key => $sale) {


                    //         SalesController::updateMileStone($sale, (float)  $sale['tot_sale_amount']);
                    //     }
                    //     $sb->update(['status' => 'Success']);
                    // }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }

            return true;
        });
    }
}
