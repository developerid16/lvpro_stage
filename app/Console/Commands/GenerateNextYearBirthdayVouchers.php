<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reward;
use App\Models\RewardLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateNextYearBirthdayVouchers extends Command
{
    protected $signature = 'voucher:generate-next-year';
    protected $description = 'Generate next year Jan-Dec birthday vouchers based on December template';

    public function handle()
    {
        DB::beginTransaction();

        try {

            $currentYear = now()->year;
            $nextYear    = $currentYear + 1;

            // Get December voucher of current year
            $decMonth = $currentYear . '-12';

            $decVouchers = Reward::where('type', '2')
                ->where('month', $decMonth)
                ->get();

            if ($decVouchers->isEmpty()) {
                $this->error('No December voucher found.');
                return;
            }

            foreach ($decVouchers as $template) {

                for ($m = 1; $m <= 12; $m++) {

                    $monthValue = Carbon::create($nextYear, $m, 1)->format('Y-m');

                    // Skip if already exists
                    if (Reward::where('type', '2')->where('month', $monthValue)->exists()) {
                        continue;
                    }

                    $newReward = $template->replicate();
                    $newReward->month      = $monthValue;
                    $newReward->from_month = $monthValue;
                    $newReward->to_month   = $monthValue;
                    $newReward->created_at = now();
                    $newReward->updated_at = now();
                    $newReward->save();

                    // Copy locations
                    $locations = RewardLocation::where('reward_id', $template->id)->get();

                    foreach ($locations as $loc) {
                        $newLoc = $loc->replicate();
                        $newLoc->reward_id = $newReward->id;
                        $newLoc->save();
                    }
                }
            }

            DB::commit();
            $this->info('Next year vouchers generated successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();
            $this->error($e->getMessage());
        }
    }
}