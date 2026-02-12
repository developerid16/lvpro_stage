<?php


namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Http\Controllers\Controller;

use App\Models\Location;
use App\Models\PartnerCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class LocationController extends Controller
{

    function __construct()
    {

        $this->view_file_path = "admin.locations.";
        $permission_prefix = $this->permission_prefix = 'locations';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Locations',
            'module_base_url' => url('admin/locations')
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
        $query = Location::where('company_id', $request->company_id);

        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'code', 'status', 'address']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['code'] = $row->code;
            $final_data[$key]['address'] = $row->address;
            $final_data[$key]['lease_duration'] = ($row->start_date && $row->end_date)
                                                    ? \Carbon\Carbon::parse($row->start_date)->format('d-m-Y') . ' / ' .
                                                    \Carbon\Carbon::parse($row->end_date)->format('d-m-Y')
                                                    : '';
            $final_data[$key]['status'] = $row->status;



            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'code' => 'required|unique:locations,code',
            'name' => 'required|unique:locations,name',
            'company_id' => 'required|exists:partner_companies,id',

            'address' => 'required',
            'status' => 'required',
            'start_date' => 'nullable',
            'end_date' => 'nullable',

        ]);

        Location::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Location Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $locationsCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        
         $this->layout_data = [
            'title' => 'Participating Merchant',
        ];
        $this->layout_data['data'] = Location::find($id);
        $this->layout_data['company_data'] = PartnerCompany::find($this->layout_data['data']->company_id);

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
            "code" => "required|unique:locations,code,$id",
            "name" => "required|unique:locations,name,$id",
            'address' => 'required',
            'company_id' => 'required|exists:partner_companies,id',
            'status' => 'required',
            'start_date' => 'nullable',
            'end_date' => 'nullable',

        ]);

        if (!empty($post_data['end_date'])) {
            $endDate = Carbon::parse($post_data['end_date'])->endOfDay();

            if ($endDate->lt(now())) {
                // Force disable if end_date passed
                $post_data['status'] = 'Disabled';
            }else{
                 $post_data['status'] = 'Active';
            }
        }
        
        Location::find($id)->update($post_data);
        return response()->json(['status' => 'success', 'message' => 'Location Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Location::where('id', $id)->delete();
        AdminLogger::log('delete', Location::class, $id);
        return response()->json(['status' => 'success', 'message' => 'Location Delete Successfully']);
    }
}
