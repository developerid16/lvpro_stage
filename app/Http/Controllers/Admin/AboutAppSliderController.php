<?php

namespace App\Http\Controllers\Admin;

use App\Models\AboutAppBanner;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AboutAppSliderController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.about-app-banner.";
        $permission_prefix = $this->permission_prefix = 'about-app-banner';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'About App Banner',
            'module_base_url' => url('admin/about-app-banner')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
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
        $query = AboutAppBanner::query();

        $query = $this->get_sort_offset_limit_query($request, $query, ['ur', 'image', 'status']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
       

            $final_data[$key]['image'] = "<a href='" . asset("images/$row->image") . "' data-lightbox='set-$row->id'> <img src='" . asset("images/$row->image") . "' class='avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle' data-lightbox='lightbox' alt='img'></a>";

            $final_data[$key]['status'] = $row->status;

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
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
    // public function create()
    // {
    //     abort(404);
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $post_data = $this->validate($request, [
    //         'url' => 'required|url',
    //         'image' => 'sometimes|required|image',
    //         'status' => 'required',
    //     ]);

    //     if ($request->hasFile('image')) {
    //         $imageName = time() . rand() . '.' . $request->image->extension();
    //         $request->image->move(public_path('images'), $imageName);
    //         $post_data['image'] = $imageName;
    //     }


    //     AboutAppBanner::create($post_data);

    //     return response()->json(['status' => 'success', 'message' => 'Reward Created Successfully']);
    // }

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
        $this->layout_data['data'] = AboutAppBanner::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post_data = $this->validate($request, [
            'image' => 'sometimes|required|image',
            'status'=> 'required'
        ]);

        $rd = AboutAppBanner::find($id);
        if ($request->hasFile('image')) {
            $imageName = time() . rand() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $post_data['image'] = $imageName;
            try {
                unlink(public_path("images/$rd->image"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }


        $rd->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Reward Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     AboutAppBanner::where('id', $id)->delete();
    //     return response()->json(['status' => 'success', 'message' => 'Reward Delete Successfully']);
    // }
}
