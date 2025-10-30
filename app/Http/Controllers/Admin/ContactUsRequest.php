<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactUsRequest as ModelsContactUsRequest;
use Illuminate\Http\Request;

class ContactUsRequest extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.contact-us.";
        $permission_prefix = $this->permission_prefix = 'contact-us';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Contact Us Request',
            'module_base_url' => url('admin/contact-us')
        ];

        $this->middleware("permission:$permission_prefix-list", ['only' => ['index', 'datatable', 'show']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = ModelsContactUsRequest::query();

        $query = $this->get_sort_offset_limit_query($request, $query, ['category', 'subject', 'name']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['category'] = $row->category;
            $final_data[$key]['subject'] = $row->subject;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));




            $url = url("admin/contact-us/$row->id/show");
            $action = "<div class='d-flex gap-3'><a href='$url' target='_blank' class='edit' data-id='$row->id'><i class='mdi mdi-eye text-primary action-icon font-size-18'></i></a></div>";


            $final_data[$key]['action'] = $action . "</div>";
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show($id)
    {
        //
        $data  = ModelsContactUsRequest::findOrFail($id);
        return view($this->view_file_path . "show", compact('data'));
    }
}
