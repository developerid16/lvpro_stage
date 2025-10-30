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

class SendSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sms;
    protected $number;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sms, $number)
    {
        $this->sms = $sms;
        $this->number = $number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("LOG SMS ");
        Log::info($this->sms );
        Log::info($this->number);
        Log::info("LOG END SMS");
        try {
            smsSend($this->sms, $this->number);
        } catch (Exception $exception) { 
            Log::error($exception);
        }
    }

}
