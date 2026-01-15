<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
   use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.merchant.";
        $permission_prefix = $this->permission_prefix = 'merchants';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Merchant',
            'module_base_url' => url('admin/merchants')
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
        $qb = Merchant::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'logo',
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

            $createdAt =  $row->created_at->format(config('safra.date-format'));
            $updatedAt =  $row->updated_at->format(config('safra.date-format'));

            // -------------------------
            // ACTION BUTTONS
            // -------------------------
            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            $action .= "<a href='" . url('admin/merchant/' . $row->id . '/club-location') . "'  class=''>
                <i class='mdi mdi-map-marker-multiple text-primary action-icon font-size-18'></i>
            </a>
            <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            </a>";
            


            $action .= "</div>";



            $final_data[$i] = [             
                'sr_no'     => $index,
                'name'      => $row->name,
                'logo' => "<img src='" . asset('uploads/image/'.$row->logo) . "' width='50' height='50'>",
                'status'    => $row->status,
                'created_at'=> $createdAt,
                'updated_at'=> $updatedAt,
                'action'    => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }

    /* -----------------------------------------------------
     * SHOW CREATE FORM MODAL
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
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'logo'   => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'   => 'Merchant name is required',
            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
            'logo.required'   => 'Logo is required',
            'logo.image'      => 'Logo must be an image',
            'logo.mimes'      => 'Allowed formats: jpg, jpeg, png, webp',
            'logo.max'        => 'Logo size must be less than 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // -------------------------
        // Upload logo
        // -------------------------
        $path = public_path('uploads/image');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file = $request->file('logo');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $file->move($path, $filename);

        // -------------------------
        // Store merchant
        // -------------------------
        Merchant::create([
            'name'   => $request->name,
            'status' => $request->status,
            'logo'   => $filename,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Merchant Created Successfully'
        ]);
    }


    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Merchant::findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * UPDATE MERCHANT
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $merchant = Merchant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'logo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'   => 'Merchant name is required',
            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
            'logo.image'      => 'Logo must be an image',
            'logo.mimes'      => 'Allowed formats: jpg, jpeg, png, webp',
            'logo.max'        => 'Logo size must be less than 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // -------------------------
        // Prepare update data
        // -------------------------
        $post_data = [
            'name'   => $request->name,
            'status' => $request->status,
        ];

        // -------------------------
        // Upload logo if provided
        // -------------------------
        if ($request->hasFile('logo')) {

            $path = public_path('uploads/image');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // delete old image
            if ($merchant->logo && file_exists(public_path($merchant->logo))) {
                unlink(public_path($merchant->logo));
            }

            $file = $request->file('logo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($path, $filename);

            $post_data['logo'] = $filename;
        }

        // -------------------------
        // Update merchant
        // -------------------------
        $merchant->update($post_data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Merchant Updated Successfully'
        ]);
    }


    /* -----------------------------------------------------
     * DELETE MERCHANT
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        Merchant::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Merchant Deleted Successfully']);
    }


    /* -----------------------------------------------------
     * LOCATION PAGE
     * ----------------------------------------------------- */
    public function location($id)
    {
        $this->layout_data['merchant'] = Merchant::findOrFail($id);
        return view($this->view_file_path . "location")->with($this->layout_data);
    }
}
