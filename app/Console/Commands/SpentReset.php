<?php

namespace App\Console\Commands;

use App\Models\Tier;
use App\Models\UserTier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SpentReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spent:reset';

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
        //
        $tier = Tier::orderBy('t_order', 'ASC')->first();


        $endDate = Carbon::now()->subDay();
        
        $userTires = UserTier::where([['status', 'Active']])->whereDate('end_at', $endDate)->get();
        foreach ($userTires as $value) {
            $value->status = 'Expired';
            $value->save();

            UserTier::create([
                'user_id' => $value->user_id,
                'tier_id' => $tier->id,
                'end_at' => tireExpiryDate(),
                'status' => "Active",
            ]);
        }
    }
}
