<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClubLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClubLocationController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.club-location.";
        $permission_prefix = $this->permission_prefix = 'club-location';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Club Location',
            'module_base_url' => url('admin/club-location')
        ];
    }

    /* -----------------------------------------------------
     * LIST PAGE (Merchant → Club Location list)
     * ----------------------------------------------------- */
    public function index($merchant)
{
    $this->layout_data['merchant_id'] = $merchant;

    return view($this->view_file_path . "index")
        ->with($this->layout_data);
}

    /* -----------------------------------------------------
     * DATATABLE
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = ClubLocation::where('merchant_id', $request->merchant_id);

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {

            $index = $startIndex + $i + 1;

            $final_data[$i] = [
                'sr_no'      => $index,
                'name'       => $row->name,
                'status'     => $row->status,
                'created_at' =>  $row->created_at->format(config('safra.date-format')),
                'updated_at' =>  $row->updated_at->format(config('safra.date-format')),

                'action' => "<div class='d-flex gap-3'>" .
                                "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                                    <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                                </a>
                                <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                                    <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                                </a>" .                               
                            "</div>",
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }

    /* -----------------------------------------------------
     * CREATE FORM
     * ----------------------------------------------------- */
    public function create($merchant)
    {
        $this->layout_data['merchant_id'] = $merchant;
        $this->layout_data['data'] = null;

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|exists:merchants,id',
            'name'        => 'required|string|max:255',
            'status'      => 'required|in:Active,Inactive',
        ], [
            'merchant_id.required' => 'Merchant is required',
            'merchant_id.exists'   => 'Invalid merchant selected',
            'name.required'        => 'Name is required',
            'status.required'      => 'Status is required',
            'status.in'            => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // validated data
        $post_data = $validator->validated();


        ClubLocation::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Location Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT FORM
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $location = ClubLocation::findOrFail($id);

        $this->layout_data['data'] = $location;
        $this->layout_data['merchant_id'] = $location->merchant_id; // FIX

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html'   => $html
        ]);
    }


    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ], [
            'name.required'   => 'Name is required',
            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // validated data
        $post_data = $validator->validated();
        ClubLocation::findOrFail($id)->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Location Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        ClubLocation::where('id', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Location Deleted Successfully']);
    }
}
