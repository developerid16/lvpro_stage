<?php

namespace App\Console\Commands;

use App\Models\KeyPassbookCredit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KeyExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:expiry';

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

        $endDate = Carbon::now()->subDay(); // alwase 30/11/XXXX
        // $endDate = Carbon::parse('2024-11-30'); // alwase 30/11/XXXX
        // dd($endDate);
        $keys  = KeyPassbookCredit::with('user')->select('*', DB::raw('count(*) as count'), DB::raw('sum(remain_keys) as rk'))->where([['remain_keys', '>', 0]])->whereDate('expiry_date', $endDate)->groupBy('user_id')->get();

        foreach ($keys as $key) {
            $user = $key->user;
            if ($user) {
                $ek = $key['rk'];
                Log::info("User ID: " . $user->id . " AK is " . $user->available_key . " EK is " . $ek);
                $user->decrement('available_key', $ek);
                // $bk = $key->user->available_key;
            }
        }
    }
}
