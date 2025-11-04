<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PartnerCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerCompanyController extends Controller
{

    function __construct()
    {

        $this->view_file_path = "admin.partner-company.";
        $permission_prefix = $this->permission_prefix = 'partner-company';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Partner Company',
            'module_base_url' => url('admin/partner-company')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
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
        $query = PartnerCompany::query();

        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'code', 'status']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['code'] = $row->code;
            $final_data[$key]['status'] = $row->status;



            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            // Auth::user()->can($this->permission_prefix . '-edit')
            if (true) {
                $url = url('admin/partner-company/' . $row->id . '/locations');
                $action .= "<a href='$url' title='Manage Locations' class='' data-id='$row->id'><i class='mdi mdi-map-marker-multiple text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
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
    public function locationsIndex($id)
    {
        $this->layout_data['company_data'] = PartnerCompany::find($id);
        $this->layout_data['title'] = "Locations of " . $this->layout_data['company_data']->name;

        $this->layout_data['module_base_url'] =  url('admin/locations');
        return view("admin.locations.index", $this->layout_data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'code' => 'required|unique:users,email',
            'name' => 'required|unique:users,phone',
            'status' => 'required',

        ]);

        PartnerCompany::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Partner Company Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(PartnerCompany $aircrewCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $this->layout_data['data'] = PartnerCompany::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $post_data = $this->validate($request, [
            "code" => "required|unique:users,email,$id",
            "name" => "required|unique:users,phone,$id",
            'status' => 'required',

        ]);
        PartnerCompany::find($id)->update($post_data);
        return response()->json(['status' => 'success', 'message' => 'Partner Company Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        PartnerCompany::where('id', $id)->delete();
        Location::where('company_id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Partner Company Delete Successfully']);
    }
}
