<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\UserPurchasedReward;
use App\Models\AppUser;

class RewardExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reward:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expired user buy reward';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //
        UserPurchasedReward::where('status', 'Purchased')->whereDate('expiry_date', Carbon::today())->update([
            'status' => 'Expired'
        ]);
        AppUser::where('user_type', 'Airport Pass Holder')->whereDate('expiry_date', Carbon::today())->update([
            'status' => 'Expired'
        ]);

        // webapp
    }
}
