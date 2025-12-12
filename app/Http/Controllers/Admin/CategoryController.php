<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.category.";
        $permission_prefix = $this->permission_prefix = 'category';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Reward Category',
            'module_base_url' => url('admin/category')
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
        $qb = Category::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

            $createdAt = $row->created_at ? $row->created_at->format('d-m-Y h:i:s A') : '';
            $updatedAt = $row->updated_at ? $row->updated_at->format('d-m-Y h:i:s A') : '';

            // -------------------------
            // ACTION BUTTONS
            // -------------------------
            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            $action .= "
             <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                            <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                        </a>";
            


            $action .= "</div>";



            $final_data[$i] = [
                'sr_no'     => $index,
                'name'      => $row->name,                
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
     * STORE category
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
        ]);
       

        Category::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Category Created Successfully']);
    }


    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Category::findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * UPDATE category
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
        ]);

        
        $category->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Category Updated Successfully']);
    }


    /* -----------------------------------------------------
     * DELETE category
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        Category::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Category Deleted Successfully']);
    }

}
