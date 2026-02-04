<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParticipatingMerchant;
use Illuminate\Support\Facades\Auth;

class ParticipatingMerchantController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.participating-merchant.";
        $permission_prefix = $this->permission_prefix = 'participating-merchant';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Participating Merchant',
            'module_base_url' => url('admin/participating-merchant')
        ];
    }


    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index(Request $request)
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    /* -----------------------------------------------------
     * DATATABLE AJAX
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = ParticipatingMerchant::query();

        if (auth()->user()->role != 1) { // not Super Admin
            $qb->where('added_by', auth()->id());
        }

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex       = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {

            $index = $startIndex + $i + 1;

            $createdAt =  $row->created_at->format(config('safra.date-format'));
            $updatedAt =  $row->updated_at->format(config('safra.date-format'));

            // ACTION BUTTONS
            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }

            // Participating Merchant Location Redirect
            $action .= "<a href='" . url('admin/participating-merchant/' . $row->id . '/location') . "'>
                <i class='mdi mdi-map-marker-multiple text-primary action-icon font-size-18'></i>
            </a>
            <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            </a>";

            $action .= "</div>";

            $final_data[$i] = [
                'sr_no'      => $index,
                'name'       => $row->name,
                'status'     => $row->status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                'action'     => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }


    /* -----------------------------------------------------
     * CREATE MODAL
     * ----------------------------------------------------- */
    public function create()
    {
        $this->layout_data['data'] = null;

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * STORE MERCHANT
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);       

        ParticipatingMerchant::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Merchant Created Successfully']);
    }


    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = ParticipatingMerchant::findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * UPDATE MERCHANT
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $merchant = ParticipatingMerchant::findOrFail($id);

        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $merchant->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Merchant Updated Successfully']);
    }


    /* -----------------------------------------------------
     * DELETE MERCHANT
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        ParticipatingMerchant::where('id', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Merchant Deleted Successfully']);
    }


    /* -----------------------------------------------------
     * PARTICIPATING MERCHANT LOCATION PAGE
     * ----------------------------------------------------- */
    public function location($id)
    {
        $this->layout_data['merchant'] = ParticipatingMerchant::findOrFail($id);

        return view($this->view_file_path . "participating-location")->with($this->layout_data);
    }
}
