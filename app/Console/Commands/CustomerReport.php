<?php

namespace App\Console\Commands;

use App\Exports\CustomerReportExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class CustomerReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a customer report';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //
        Excel::store(new CustomerReportExport(), 'customer-report.xlsx');
    } 
}
