<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;

class NotifyUserOfCompletedImport {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        Log::info("Creating new controller");
    }
}