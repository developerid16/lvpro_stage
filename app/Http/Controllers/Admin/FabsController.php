<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fab;
use App\Models\Fabs;
use Illuminate\Support\Facades\Auth;

class FabsController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.fabs.";
        $permission_prefix = $this->permission_prefix = 'fabs';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Fab',
            'module_base_url' => url('admin/fabs')
        ];
    }

    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index(Request $request)
    {
        return view($this->view_file_path . "index")
            ->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * DATATABLE
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = Fabs::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'code',
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

            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                            <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                            </a>";
            }

            $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                        <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                        </a>";

            $action .= "</div>";

            $final_data[$i] = [
                'sr_no'     => $index,
                'name'      => $row->name,
                'code'      => $row->code,
                'status'    => $row->status,
                'created_at'=> $row->created_at->format(config('safra.date-format')),
                'updated_at'=> $row->updated_at->format(config('safra.date-format')),
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
     * CREATE
     * ----------------------------------------------------- */
    public function create()
    {
        $this->layout_data['data'] = null;

        $html = view($this->view_file_path . 'add-edit-modal',
            $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
            'code'   => 'required|string|max:255|unique:fabs,code',
            'status' => 'required|in:Active,Inactive',
        ]);

        Fabs::create($post_data);

        return response()->json([
            'status' => 'success',
            'message' => 'Fabs Created Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * EDIT
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Fabs::findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal',
            $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $fab = Fabs::findOrFail($id);

        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
            'code'   => 'required|string|max:255|unique:fabs,code,' . $id,
            'status' => 'required|in:Active,Inactive',
        ]);

        $fab->update($post_data);

        return response()->json([
            'status' => 'success',
            'message' => 'Fabs Updated Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * DELETE
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        Fabs::where('id', $id)->delete();
        AdminLogger::log('delete', Fabs::class, $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Fabs Deleted Successfully'
        ]);
    }
}
