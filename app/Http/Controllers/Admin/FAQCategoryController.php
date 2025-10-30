<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\FAQCategory;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class FAQCategoryController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {

        $this->view_file_path = "admin.faq-category.";
        $permission_prefix = $this->permission_prefix = 'faq';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'FAQ Category',
            'module_base_url' => url('admin/faq-category')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
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
        $query = FAQCategory::query();

        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'status'], default_sort: false);

        $final_data = [];
        $categoryData = $query['data']->get();
        $length = count($categoryData) - 1;

        foreach ($categoryData  as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['category_order'] = $row->category_order;
            $final_data[$key]['is_for'] = $row->is_for;

            $final_data[$key]['status'] = $row->status;

            $action = "<div class='d-flex gap-3'>";
            $url = url('admin/faq') . "?category_id=$row->id";
            $action .= "<a href='$url'  ><i class='mdi mdi-eye text-primary  action-icon font-size-18'></i></a>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary  action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }

            if ($key != 0) {
                $action .= "<a href='javascript:void(0)' class='order_change' data-id='$row->id' data-type='up'><i class='mdi mdi-gesture-swipe-up text-info action-icon font-size-18'></i></a>";
            }

            if ($length != $key) {
                $action .= "<a href='javascript:void(0)' class='order_change' data-id='$row->id' data-type='down'><i class='mdi mdi-gesture-swipe-down text-warning action-icon font-size-18'></i></a>";
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
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'name' => 'required|max:191',
            'category_order' => 'required',
            'status' => 'required',
            'is_for' => 'required|in:Both,FAQ,Chat Bot',
        ]);

        FAQCategory::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'FAQ Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->layout_data['data'] = FAQCategory::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post_data = $this->validate($request, [
            'name' => 'required|max:191',
            'category_order' => 'required',

            'status' => 'required',
            'is_for' => 'required|in:Both,FAQ,Chat Bot',

        ]);

        FAQCategory::find($id)->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'FAQ Category Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        FAQCategory::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'FAQ Category Delete Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function upDownCategory(Request $request)
    {
        $category = FAQCategory::findOrFail($request->id);

        if ($request->type === 'down') {
            $down = FAQCategory::where([['category_order', '<=', $category->category_order], ['id', '!=', $category->id]])->first();
            if ($down) {
                $category->category_order = $down->category_order - 1;
            }
        } else {
            $down = FAQCategory::where([['category_order', '>=', $category->category_order], ['id', '!=', $category->id]])->orderBy('category_order', 'asc')->first();
            if ($down) {
                $category->category_order = $down->category_order + 1;
            }
        }
        $category->save();

        return response()->json(['status' => 'success', 'message' => 'FAQ Category order change']);
    }
}
