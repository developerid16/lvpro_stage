<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmail  implements ShouldQueue
{


    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $emailClassRef;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $emailClassRef)
    {

        $this->email = $email;
        $this->emailClassRef = $emailClassRef;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info($this->email);
        try {
            Mail::to($this->email)->send($this->emailClassRef);
        } catch (Exception $exception) {
            Log::error($exception);
        }
    }
}
