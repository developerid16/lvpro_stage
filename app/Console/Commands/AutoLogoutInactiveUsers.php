<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AutoLogoutInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   
    /**
     * Execute the console command.
     */
    protected $signature = 'users:auto-logout';
    protected $description = 'Logout users inactive for 30 minutes';

    public function handle()
    {
        $time = now()->subMinutes(30);

        User::whereNotNull('session_id')
            ->where('last_login_at','<',$time)
            ->update([
                'session_id' => null,
                'last_login_at' => null
            ]);

        $this->info('Inactive users logged out.');
    }
}
