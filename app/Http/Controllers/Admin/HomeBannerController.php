<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeBannerController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.home-banner.";
        $permission_prefix = $this->permission_prefix = 'home-banner';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Home Banner',
            'module_base_url' => url('admin/home-banner')
        ];
    }

    /* ---------------- LIST PAGE ---------------- */
    public function index()
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    /* ---------------- DATATABLE ---------------- */
    public function datatable(Request $request)
    {
        $qb = HomeBanner::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
          'id','title','image','status','position','created_at','updated_at'
        ]);

        $rows = $result['data'];
        $start = $result['offset'] ?? 0;

        $data = [];
        $i = 0;

        foreach ($rows->get() as $row) {
            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix.'-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                                <i class='mdi mdi-pencil text-primary'></i>
                            </a>";
            }

            $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                            <i class='mdi mdi-delete text-danger'></i>
                        </a></div>";

            $data[] = [
                'sr_no' => $start + (++$i),
                'title' => $row->title,
                'image' => "<img src='".asset('uploads/banner/'.$row->image)."' width='50' height='50'>",
                'position' => $row->position,
                'status' => $row->status,
                'updated_at' => $row->updated_at->format(config('safra.date-format')),
                'created_at' => $row->created_at->format(config('safra.date-format')),
                'action' => $action
            ];
        }

        return [
            'items' => $data,
            'count' => $result['count'] ?? $rows->count()
        ];
    }

    /* ---------------- CREATE MODAL ---------------- */
    public function create()
    {
        $this->layout_data['data'] = null;
        $html = view($this->view_file_path.'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status'=>'success','html'=>$html]);
    }

    /* ---------------- STORE ---------------- */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'required|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>'error','errors'=>$validator->errors()],422);
        }

        $path = public_path('uploads/banner');
        if (!file_exists($path)) mkdir($path,0777,true);

        $file = $request->file('image');
        $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($path,$name);

        HomeBanner::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $name,
            'action_type' => $request->action_type,
            'action_value' => $request->action_value,
            'position' => $request->position ?? 0,
            'status' => $request->status,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at
        ]);

        return response()->json(['status'=>'success','message'=>'Banner Created']);
    }

    /* ---------------- EDIT ---------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = HomeBanner::findOrFail($id);
        $html = view($this->view_file_path.'add-edit-modal',$this->layout_data)->render();
        return response()->json(['status'=>'success','html'=>$html]);
    }

    /* ---------------- UPDATE ---------------- */
    public function update(Request $request, $id)
    {
        $banner = HomeBanner::findOrFail($id);

        $data = $request->only([
            'title','description','action_type','action_value',
            'position','status','start_at','end_at'
        ]);

        if ($request->hasFile('image')) {
            $path = public_path('uploads/banner');
            if ($banner->image && file_exists($path.'/'.$banner->image)) {
                unlink($path.'/'.$banner->image);
            }

            $file = $request->file('image');
            $name = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move($path,$name);
            $data['image'] = $name;
        }

        $banner->update($data);

        return response()->json(['status'=>'success','message'=>'Banner Updated']);
    }

    /* ---------------- DELETE ---------------- */
    public function destroy($id)
    {
        HomeBanner::where('id',$id)->delete();
        return response()->json(['status'=>'success','message'=>'Banner Deleted']);
    }
}
