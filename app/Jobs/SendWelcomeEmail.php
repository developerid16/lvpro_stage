<?php

namespace App\Jobs;

use App\Models\AppUser;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;


class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $type = "";
    protected $file = "";
    /**
     * Create a new job instance.
     */
    public function __construct($type, $file)
    {
        \Log::info("Working");

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
                \Log::info($row);
                if ($i == 0) {
                } else {
                    $email = $row[0];
                    \Log::info("====$email");
                    // $user = AppUser::where('email', $email)->first();
                    // if ($user) {

                
                  
                    try {
                        $provider = substr($email, strpos($email, '@') + 1);
  
                        $name = $row[1];
                        \Mail::send('email.edm', [
                            'name' => $row[1],
                            'unique_id' => $row[2],
                                    "provider" => $provider
                        ], function ($message) use ($email, $name) {
    
                            $message->to($email, $name);
                            $message->subject('Lancôme x Shilla Access Exclusive Event – You’re Invited!');
                            $message->priority(3);
                           // $message->attach('https://image.theshillaaccess.sg/HowToResetPassword.png');
                        });
                        EmailLog::create([
                            'email' => $row[0],
                            'type' => $type,
                            'status' => 'Send',
                        ]);
                        //code...
                    } catch (\Throwable $th) {
                        //throw $th;
                        \Log::info("Error ON EMAIL " .  $email);
                        EmailLog::create([
                            'email' => $row[0],
                            'type' => $type,
                            'status' => 'Error',
                        ]);
                    }
                   
                }
                // else {

                //     EmailLog::create([
                //         'email' => $row[0],
                //         'type' => $type,
                //         'status' => 'User Not FoundF',
                //     ]);
                // }
                $i++;
            }
        }
    }
}
