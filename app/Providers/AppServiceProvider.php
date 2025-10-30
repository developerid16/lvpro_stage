<?php

namespace App\Providers;

use App\Http\Controllers\Admin\SalesController;
use App\Models\Sale;
use App\Models\SaleBatch;
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
        //
        Paginator::useBootstrap();

        URL::forceScheme('https');
        Schema::defaultStringLength(191);
        Queue::after(function (JobProcessed $event) {

            $payload = $event->job->payload();

            if ($payload['displayName'] === "Maatwebsite\Excel\Jobs\AfterImportJob") {

                try {
                    //code...
                    $sb = SaleBatch::where('status', 'In Process')->orderBy('id', 'desc')->first();
                    if ($sb) {
                        $sales = Sale::where('batch_id', $sb->id)->selectRaw('*, sum(sale_amount) as tot_sale_amount')->groupBy('ref')->get();
                        foreach ($sales as $key => $sale) {


                            SalesController::updateMileStone($sale, (float)  $sale['tot_sale_amount']);
                        }
                        $sb->update(['status' => 'Success']);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }

            return true;
        });
    }
}
