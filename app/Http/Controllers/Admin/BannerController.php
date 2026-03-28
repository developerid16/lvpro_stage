<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DepartmentActivityLogger;
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

        $this->middleware("active.permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("active.permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("active.permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("active.permission:$permission_prefix-delete", ['only' => ['destroy']]);
        $this->middleware("active.permission:$permission_prefix-activity-log", ['only' => ['activityLog']]);

        $this->middleware(function ($request, $next) {
            $activeDeptId = session('active_department_id');
            $user = Auth::user();

            $activeRoles = $user->roles->filter(function ($role) use ($activeDeptId) {
                return (string)$role->department === (string)$activeDeptId;
            });

            if ($activeRoles->isEmpty()) {
                $activeRoles = $user->roles;
            }

            $activeRole = $activeRoles->first();

            $this->activeDeptId     = $activeDeptId;
            $this->activeLocationId = session('active_club_location_id');
            $this->activeRoleId     = $activeRole?->id;

            return $next($request);
        });
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
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('active_department_id', $this->activeDeptId);
            $query->where('active_club_location_id', $this->activeLocationId);
            $query->where('active_role_id', $this->activeRoleId);
        }
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

            $activePermissions = session('active_permissions', []);

            $canEdit   = in_array($this->permission_prefix . '-edit',   $activePermissions) || Auth::user()->hasRole('Super Admin');
            $canDelete = in_array($this->permission_prefix . '-delete', $activePermissions) || Auth::user()->hasRole('Super Admin');

            $action = "<div class='d-flex gap-3'>";

            if ($canEdit) {
                $action .= "<a href='javascript:void(0)' 
                    class='edit' 
                    data-id='$row->id'
                    title='Edit'>
                    <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                </a>";
            }

            if ($canDelete) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'>
                                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                            </a>";
            }

            $action .= "<a target='_blank' href='" . url('admin/banner/' . $row->id . '/activity-log') . "' 
                            class='activity-log text-primary' 
                            data-id='$row->id'
                            title='Fabs Activity Log'>
                            <i class='mdi mdi-history action-icon font-size-18'></i>
                        </a>";

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
        $validated['active_department_id']      = $this->activeDeptId ?? NULL;
        $validated['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $validated['active_role_id']            = $this->activeRoleId ?? NULL;
        $validated['added_by'] = Auth::user()->id;

        $banner = Banner::create($validated);
        DepartmentActivityLogger::log(
            'create',
            'banner',
            $banner->id,
            $banner->name,
            [],
            $banner->toArray(),
            "Banner '{$banner->name}' Created Successfully."
        );
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
        $oldData = $banner->toArray();
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
        $validated['active_department_id']      = $this->activeDeptId ?? NULL;
        $validated['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $validated['active_role_id']            = $this->activeRoleId ?? NULL;
        $validated['added_by'] = Auth::user()->id;
        $banner->update($validated);

        DepartmentActivityLogger::log(
            'update',
            'banner',
            $banner->id,
            $banner->name,
            $oldData,
            $banner->fresh()->toArray(),
            "Banner '{$banner->name}' Updated Successfully."
        );

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

        $banner = Banner::findOrFail($id);

        if ($banner->desktop_image && file_exists(public_path('uploads/image/' . $banner->desktop_image))) {
            unlink(public_path('uploads/image/' . $banner->desktop_image));
        }

        if ($banner->mobile_image && file_exists(public_path('uploads/image/' . $banner->mobile_image))) {
            unlink(public_path('uploads/image/' . $banner->mobile_image));
        }
        $bannerName = $banner->name;
        $bannerData = $banner->toArray();
        $banner->forceDelete();
        DepartmentActivityLogger::log(
            'force_delete',
            'banner',
            $id,
            $bannerName,
            $bannerData,
            [],
            "Banner '{$bannerName}' permanently deleted."
        );

        AdminLogger::log('delete',Banner::class,$id);

        return response()->json([
            'status'=>'success',
            'message'=>'Banner deleted successfully'
        ]);
    }
}