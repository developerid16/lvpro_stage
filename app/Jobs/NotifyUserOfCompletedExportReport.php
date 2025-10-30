<?php

namespace App\Jobs;

use App\Mail\ReportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\ReportJob;

class NotifyUserOfCompletedExportReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data = [];
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $user = User::where('id', $this->data['user_id'])->first();

        ReportJob::where('udid', $this->data['udid'])->update([
            'status' => "Success"
        ]);
        $data = [];
        Mail::to($user->email)->send(
            new ReportNotification($data)
        );

        //
    }
}
