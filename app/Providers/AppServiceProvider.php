<?php

namespace App\Providers;

use App\Observers\ActivityObserver;
use App\Http\Controllers\Admin\SalesController;
use App\Models\Announcement;
use App\Models\AppUser;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\ContactUsRequest;
use App\Models\ContentManagement;
use App\Models\CustomLocation;
use App\Models\DashboardPopup;
use App\Models\Department;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\ParticipatingMerchantLocation;
use App\Models\Purchase;
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
use App\Models\User;
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
        //All Module Logs
        Reward::observe(ActivityObserver::class);
        User::observe(ActivityObserver::class);
        Announcement::observe(ActivityObserver::class);
        AppUser::observe(ActivityObserver::class);
        Category::observe(ActivityObserver::class);
        ClubLocation::observe(ActivityObserver::class);
        ContactUsRequest::observe(ActivityObserver::class);
        ContentManagement::observe(ActivityObserver::class);
        CustomLocation::observe(ActivityObserver::class);
        DashboardPopup::observe(ActivityObserver::class);
        Department::observe(ActivityObserver::class);
        Location::observe(ActivityObserver::class);
        Merchant::observe(ActivityObserver::class);
        ParticipatingLocations::observe(ActivityObserver::class);
        ParticipatingMerchant::observe(ActivityObserver::class);
        ParticipatingMerchantLocation::observe(ActivityObserver::class);
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
