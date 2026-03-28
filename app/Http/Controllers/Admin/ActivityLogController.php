<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class ActivityLogController extends Controller
{
    public function activityLog($record_id)
    {
        $department_activity_logs = DB::table('department_activity_logs')
            ->where('record_id', $record_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->layout_data['logs']      = $department_activity_logs;
        $this->layout_data['record_id'] = $record_id;

        return view("admin.activity-log")->with($this->layout_data);
    }
}
