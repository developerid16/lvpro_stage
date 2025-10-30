<?php

namespace App\Http\Controllers\Admin;

use App\Models\FAQ;

use App\Models\FAQCategory;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FAQController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {

        $this->view_file_path = "admin.faq.";
        $permission_prefix = $this->permission_prefix = 'faq';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'FAQ',
            'module_base_url' => url('admin/faq')
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
        $this->layout_data['category'] = FAQCategory::findOrFail($request->category_id);

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = FAQ::query()->where('category_id', $request->category_id);

        $query = $this->get_sort_offset_limit_query($request, $query, ['question', 'answer', 'status'], default_sort: false);

        $final_data = [];
        $faqData = $query['data']->get();
        $length = count($faqData) - 1;
        foreach ($faqData as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['question'] = $row->question;
            $final_data[$key]['answer'] = $row->answer;
            $final_data[$key]['faq_order'] = $row->faq_order;

            $final_data[$key]['status'] = $row->status;

            $action = "<div class='d-flex gap-3'>";
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
            'question' => 'required|max:191',
            'answer' => 'required',
            'status' => 'required',
            'category_id' => 'required',
            'faq_order' => 'required',
        ]);

        FAQ::create($post_data);

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
        $this->layout_data['data'] = FAQ::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post_data = $this->validate($request, [
            'question' => 'required|max:191',
            'answer' => 'required',
            'status' => 'required',
            'faq_order' => 'required',

        ]);

        FAQ::find($id)->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'FAQ Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        FAQ::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'FAQ Delete Successfully']);
    }

    public function upDownFaq(Request $request)
    {
        $faq = FAQ::findOrFail($request->id);

        if ($request->type === 'down') {
            $down = FAQ::where([['faq_order', '<=', $faq->faq_order], ['id', '!=', $faq->id], ['category_id', $faq->category_id]])->first();
            if ($down) {
                $faq->faq_order = $down->faq_order - 1;
            }
        } else {
            $down = FAQ::where([['faq_order', '>=', $faq->faq_order], ['id', '!=', $faq->id], ['category_id', $faq->category_id]])->orderBy('faq_order', 'asc')->first();
            if ($down) {
                $faq->faq_order = $down->faq_order + 1;
            }
        }
        $faq->save();

        return response()->json(['status' => 'success', 'message' => 'FAQ  order change']);
    }
}
