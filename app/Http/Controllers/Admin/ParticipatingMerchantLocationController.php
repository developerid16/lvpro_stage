<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParticipatingMerchantLocation;
use App\Models\ParticipatingMerchant;
use App\Models\ClubLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ParticipatingMerchantLocationController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.participating-merchant-location.";
        $permission_prefix = $this->permission_prefix = 'participating-merchant-location';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Participating Merchant Outlet',
            'module_base_url' => url('admin/participating-merchant-location')
        ];
    }

    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index($merchant)
    {
        // Participating merchant (NOT Merchant)
        $pm = ParticipatingMerchant::findOrFail($merchant);

        $this->layout_data['participating_merchant_id'] = $pm->id;
        $this->layout_data['participating_merchant']   = $pm;

        // Club Locations dropdown depends on participating merchant
        $this->layout_data['locations'] = ClubLocation::all();

        // Participating merchant list dropdown
        $this->layout_data['merchants'] = ParticipatingMerchant::where('id',$merchant)->orderBy('name')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * DATATABLE
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = ParticipatingMerchantLocation::where(
            'participating_merchant_id',
            $request->participating_merchant_id
        );
        if (auth()->user()->role != 1) { // not Super Admin
            $qb->where('added_by', auth()->id());
        }

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'code',
            'start_date',
            'end_date',
            'club_location_id',
            'participating_merchant_id',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rows = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $final_data[$i] = [
                'sr_no'        => $startIndex + $i + 1,
                'name'         => $row->name,
                'code'         => $row->code,
                'start_date'   => $row->start_date->format(config('safra.date-format')),
                'end_date'     => $row->end_date->format(config('safra.date-format')),
                'club_location'=> optional($row->clubLocation)->name,
                'status'       => $row->status,
                'created_at'   =>  $row->created_at->format(config('safra.date-format')),
                'updated_at'   =>  $row->updated_at->format(config('safra.date-format')),

                'action' =>
                    "<div class='d-flex gap-3'>
                        <a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                            <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                        </a>

                        <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                            <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                        </a>
                    </div>",
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rows->count(),
        ];
    }


    /* -----------------------------------------------------
     * CREATE MODAL
     * ----------------------------------------------------- */
    public function create($merchant)
    {
        $this->layout_data['participating_merchant_id'] = $merchant;
        $this->layout_data['data'] = null;

        $this->layout_data['locations'] = ClubLocation::all();
        $this->layout_data['merchants'] = ParticipatingMerchant::where('id',$merchant)->get();

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'participating_merchant_id' => 'required|exists:participating_merchants,id',
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:100',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'club_location_id'  => 'required|exists:club_locations,id',
            'status'            => 'required|in:Active,Inactive',
        ], [
            'participating_merchant_id.required' => 'Participating merchant is required',
            'participating_merchant_id.exists'   => 'Invalid participating merchant',

            'name.required' => 'Name is required',
            'code.required' => 'Code is required',

            'start_date.required' => 'Start date is required',
            'start_date.date'     => 'Start date must be a valid date',

            'end_date.required' => 'End date is required',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',

            'club_location_id.required' => 'Club location is required',
            'club_location_id.exists'   => 'Invalid club location',

            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();

        ParticipatingMerchantLocation::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Participating Merchant Location Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $row = ParticipatingMerchantLocation::findOrFail($id);

        $this->layout_data['data'] = $row;
        $this->layout_data['participating_merchant_id'] = $row->participating_merchant_id;

        $this->layout_data['locations'] = ClubLocation::all();
        $this->layout_data['merchants'] = ParticipatingMerchant::where('id',$row->participating_merchant_id)->get();

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:100',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'club_location_id'  => 'required|exists:club_locations,id',
            'status'            => 'required|in:Active,Inactive',
        ], [
            'name.required' => 'Name is required',
            'code.required' => 'Code is required',

            'start_date.required' => 'Start date is required',
            'start_date.date'     => 'Start date must be a valid date',

            'end_date.required' => 'End date is required',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',

            'club_location_id.required' => 'Club location is required',
            'club_location_id.exists'   => 'Invalid club location',

            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();

        ParticipatingMerchantLocation::findOrFail($id)->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Participating Merchant Location Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        ParticipatingMerchantLocation::destroy($id);

        return response()->json(['status' => 'success', 'message' => 'Participating Merchant Location Deleted Successfully']);
    }
}
