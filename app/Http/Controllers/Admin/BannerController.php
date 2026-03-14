<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{

    function __construct()
    {

        $this->view_file_path = "admin.banner.";

        $permission_prefix = $this->permission_prefix = 'banner';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Banner',
            'module_base_url' => url('admin/banner')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Banner listing
     */
    public function index()
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    /**
     * Datatable
     */
    public function datatable(Request $request)
    {

        $query = Banner::query()->orderBy('id', 'desc');

        $result = $this->get_sort_offset_limit_query(
            $request,
            $query,
            ['header','button_text','status']
        );

        $rows  = $result['data'];
        $start = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $index = $start + $i + 1;
            
            $final_data[$i] = [
                'id' => $row->id,
                'sr_no' => $index,
                'header' => $row->header,
                'link' => "<a href='{$row->link}' target='_blank'>Open</a>",
                'button_text' => $row->button_text,
                'description' => \Str::limit($row->description,50),
                'status' => $row->status ? 'Active' : 'Inactive'
            ];
            $final_data[$i]['desktop_image'] = imagePreviewHtml("uploads/image/{$row->desktop_image}");
            $final_data[$i]['mobile_image'] = imagePreviewHtml("uploads/image/{$row->mobile_image}");

            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {

                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                    <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                </a>";
            }

            if (Auth::user()->can($this->permission_prefix . '-delete')) {

                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                    <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                </a>";
            }

            $final_data[$i]['action'] = $action . "</div>";

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'],
        ];
    }


    /**
     * Store Banner
     */
    public function store(Request $request)
    {

        $rules = [
            'header' => 'required|max:255',
            'button_text' => 'required|max:50',
            'link' => 'nullable|url|max:255',
            'description' => 'required|max:500',
            'desktop_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'mobile_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('desktop_image')) {

            $name = generateHashFileName($request->desktop_image->getClientOriginalName());
            $request->desktop_image->move(public_path('uploads/image'), $name);
            $validated['desktop_image'] = $name;
        }

        if ($request->hasFile('mobile_image')) {

            $name = generateHashFileName($request->mobile_image->getClientOriginalName());
            $request->mobile_image->move(public_path('uploads/image'), $name);
            $validated['mobile_image'] = $name;
        }

        Banner::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Banner created successfully'
        ]);
    }

    /**
     * Edit Banner
     */
    public function edit(string $id)
    {

        $this->layout_data['data'] = Banner::find($id);

        $html = view($this->view_file_path.'add-edit-modal',$this->layout_data)->render();

        return response()->json([
            'status'=>'success',
            'html'=>$html
        ]);
    }

    /**
     * Update Banner
     */
    public function update(Request $request, string $id)
    {

        $rules = [
            'header' => 'required|max:255',
            'button_text' => 'required|max:50',
            'link' => 'nullable|url|max:255',
            'description' => 'required|max:500',
            'desktop_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            return response()->json([
                'status'=>'error',
                'errors'=>$validator->errors()
            ],422);
        }

        $validated = $validator->validated();

        $banner = Banner::findOrFail($id);

        if ($request->hasFile('desktop_image')) {

            $imageName = generateHashFileName($request->desktop_image->getClientOriginalName());
            $request->desktop_image->move(public_path('uploads/image'), $imageName);

            $validated['desktop_image'] = $imageName;
        }

        if ($request->hasFile('mobile_image')) {

            $imageName = generateHashFileName($request->mobile_image->getClientOriginalName());
            $request->mobile_image->move(public_path('uploads/image'), $imageName);

            $validated['mobile_image'] = $imageName;

            if (!empty($banner->mobile_image)) {

                $oldPath = public_path('uploads/image/'.$banner->mobile_image);

                if(file_exists($oldPath)){
                    unlink($oldPath);
                }
            }
        }

        $banner->update($validated);

        return response()->json([
            'status'=>'success',
            'message'=>'Banner updated successfully'
        ]);
    }

    /**
     * Delete Banner
     */
    public function destroy(string $id)
    {

        Banner::where('id',$id)->delete();

        AdminLogger::log('delete',Banner::class,$id);

        return response()->json([
            'status'=>'success',
            'message'=>'Banner deleted successfully'
        ]);
    }
}