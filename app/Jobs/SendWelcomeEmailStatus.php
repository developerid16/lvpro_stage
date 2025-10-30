<?php

namespace App\Jobs;

use App\Models\AppUser;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;


class SendWelcomeEmailStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $type = "";
    protected $file = "";
    /**
     * Create a new job instance.
     */
    public function __construct($type, $file)
    {
        $this->type = $type;
        $this->file = $file;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $delimiter = ',';
        $i = 0;


        $type = $this->type;
        $filePath = public_path('report') . '/' .  $this->file;
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if ($i == 0) {
                    $i++;
                } else {
                    $email = $row[0];
                    // $user = AppUser::where('email', $email)->first();
                    // if ($user) {

                        EmailLog::where([
                            ['email', $row[0]],
                            ['type', $type],
                            'status' => 'Send',
                        ])->update([
                            'status' => ''
                        ]);
                    }
                    //  else {
                    // }
                   
                }
            }
        }
    }

