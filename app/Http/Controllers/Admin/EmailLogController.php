<?php

namespace App\Http\Controllers\Admin;


use App\Jobs\SendWelcomeEmail;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Jobs\SendWelcomeEmailStatus;
use App\Models\EmailLog;
use Carbon\Carbon;

class EmailLogController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.emaillogs.";
        $this->layout_data = [
            'title' => 'Email Logs',
            'module_base_url' => url('admin/email-log')
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = EmailLog::query()->latest();

        $query = $this->get_sort_offset_limit_query($request, $query, ['email', 'type', 'status'], default_sort: false);

        $final_data = [];
        $faqData = $query['data']->get();
        foreach ($faqData as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['email'] = $row->email;
            $final_data[$key]['type'] = $row->type;
            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format') . " g:i:s a");

            $final_data[$key]['status'] = $row->status;
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'file' => 'required|file',
        ]);

        // Processing , Completed and Fail
        if ($request->hasFile('file')) {
            $fileName = time() . rand() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('report'), $fileName);
        }


 
        try {
            SendWelcomeEmail::dispatch($request->type, $fileName);
            // $delay = Carbon::now()->addMinutes(20);
            // SendWelcomeEmailStatus::dispatch($request->type, $fileName)->delay($delay);
        } catch (\Exception $e) {
            throw $e;
        }
        return response()->json(['status' => 'success', 'message' => 'Email Send Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailLog $emailLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailLog $emailLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailLog $emailLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailLog $emailLog)
    {
        //
    }
}
