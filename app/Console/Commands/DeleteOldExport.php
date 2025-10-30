<?php

namespace App\Console\Commands;

use App\Models\ReportJob;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteOldExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:oldreport';

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
        $oldReportJobs = ReportJob::where('created_at', '<', Carbon::now()->subDays(3))->get();
        foreach ($oldReportJobs as $job) {
            // Delete the file associated with 'udid' field
            // if ($job->udid && Storage::exists($job->udid)) {
            //     Storage::delete($job->udid);
            // }
            unlink(public_path("report/$job->udid.xlsx"));

            // Delete the record from the database
            $job->delete();
        }
    }
}
