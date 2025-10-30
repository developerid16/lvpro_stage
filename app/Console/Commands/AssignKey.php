<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\SalesCSVError;
use App\Http\Controllers\Admin\SalesController;
use App\Models\AppUser;
use App\Models\RefundSale;
use App\Models\Sale;
use App\Models\SaleBatch;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class AssignKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // SELECT * FROM `sales` WHERE `date` >= '2024-07-17' AND `date` <= '2024-07-25' AND `batch_id` != '27'
        
        $items = Sale::where('batch_id','!=', 27)->whereDate('date', '>=', '2024-07-17')->whereDate('date', '<=', '2024-07-25')->selectRaw('*,sum(sale_amount) as sum')
        ->groupBy('ref')->get();

    // TODO:OLD 
    foreach ($items as $item) {

        SalesController::updateMileStoneAssign($item, (float) $item['sum']);
    }
    $items = RefundSale::where('batch_id','!=', 27)->whereDate('date', '>=', '2024-07-17')->whereDate('date', '<=', '2024-07-25')->selectRaw('*,sum(sale_amount) as sum')
        ->groupBy('ref')->get();
    $rows = [];
    foreach ($items as $item) {

        $key = SalesController::refundSaleAssign($item, floor((float) $item['sum']));
        $item->key_earn = $key;
        $item->save();

    }
    }
}
