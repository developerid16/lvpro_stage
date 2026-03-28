<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Helpers\DepartmentActivityLogger;
use App\Models\DashboardPopup;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DashboardPopupController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.dashboardpopup.";
        $permission_prefix = $this->permission_prefix = 'dashboard-popup';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Dashboard popup',
            'module_base_url' => url('admin/dashboardpopup')
        ];

        $this->middleware("active.permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("active.permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("active.permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("active.permission:$permission_prefix-delete", ['only' => ['destroy']]);
        $this->middleware("active.permission:$permission_prefix-reorder", ['only' => ['reorder']]);
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
     * Display a listing of the resource.
     */
    public function index()
    {

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        // Always order by display_order DESC (higher first)
        $query = DashboardPopup::query()
            ->orderBy('order', 'desc');

        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('active_department_id', $this->activeDeptId);
            $query->where('active_club_location_id', $this->activeLocationId);
            $query->where('active_role_id', $this->activeRoleId);
        }

        $result = $this->get_sort_offset_limit_query(
            $request,
            $query,
            ['code', 'name', 'status', 'frequency']
        );

        $rows  = $result['data'];
        $start = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $index = $start + $i + 1;

            $final_data[$i] = [
                // 🔥 REQUIRED for drag & drop
                'id'        => $row->id,

                'sr_no'     => $index,
                'code'      => $row->code,
                'name'      => $row->name,
                'order'     => $row->order,
                'status'    => $row->status,
                'frequency' => $row->frequency,
                'date'      =>
                    optional($row->start_date)->format(config('safra.date-format')) .
                    ' to ' .
                    optional($row->end_date)->format(config('safra.date-format')),
                'image'     => "<a href='" . asset("images/$row->image") . "' data-lightbox='set-$row->id'>
                                    <img src='" . asset("images/$row->image") . "'
                                        class='avatar-sm me-3 mx-lg-auto mb-3 mt-1
                                        float-start float-lg-none rounded-circle'
                                        alt='img'>
                                </a>",
            ];

            $final_data[$i]['desktop_image'] = imagePreviewHtml("uploads/image/{$row->desktop_image}");
            $final_data[$i]['mobile_image'] = imagePreviewHtml("uploads/image/{$row->mobile_image}");

            // ---------------- ACTIONS ----------------
            // $action = "<div class='d-flex gap-3'>";
            // $action .= "<span class='text-muted drag-indicator' title='Drag to reorder'>
            //     <i class='mdi mdi-drag'></i>
            // </span>";

            // if (Auth::user()->can($this->permission_prefix . '-edit')) {
            //     $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
            //                     <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
            //                 </a>";
            // }

            // if (Auth::user()->can($this->permission_prefix . '-delete')) {
            //     $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
            //                     <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            //                 </a>";
            // }

            $activePermissions = session('active_permissions', []);

            $canEdit   = in_array($this->permission_prefix . '-edit',   $activePermissions) || Auth::user()->hasRole('Super Admin');
            $canDelete = in_array($this->permission_prefix . '-delete', $activePermissions) || Auth::user()->hasRole('Super Admin');

            $action = "<div class='d-flex gap-3'>";

            $action .= "<span class='text-muted drag-indicator' title='Drag to reorder'>
                <i class='mdi mdi-drag'></i>
            </span>";
            
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

            $action .= "<a target='_blank' href='" . url('admin/dashboardpopup/' . $row->id . '/activity-log') . "' 
                            class='activity-log text-primary' 
                            data-id='$row->id'
                            title='dashboard popup Activity Log'>
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
        $rules = [
            'name'        => 'required|string|max:25',
            'button'      => 'required|string|max:10',
            'order'       => 'required|numeric',
            'popup_type'  => 'required|in:once-a-day,always',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'url'         => 'required|url|max:255',
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

        // Upload image
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
        // Map popup_type → frequency
        $validated['frequency'] = $validated['popup_type'];
        $validated['active_department_id']      = $this->activeDeptId ?? NULL;
        $validated['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $validated['active_role_id']            = $this->activeRoleId ?? NULL;
        $validated['added_by'] = Auth::user()->id;
        $dashboardPopup = DashboardPopup::create($validated);
        DepartmentActivityLogger::log(
            'create',
            'dashboardpopup',
            $dashboardPopup->id,
            $dashboardPopup->name,
            [],
            $dashboardPopup->toArray(),
            "Dashboard Popup '{$dashboardPopup->name}' Created Successfully."
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Dashboard Popup created successfully'
        ]);
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
        $this->layout_data['data'] = DashboardPopup::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name'        => 'required|string|max:25',
            'button'      => 'required|string|max:10',
            'popup_type'  => 'required|in:once-a-day,always',
            'order'       => 'required|numeric',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'url'         => 'required|url|max:255',
            'desktop_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'mobile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $popup = DashboardPopup::findOrFail($id);
        $oldData = $popup->toArray();
        // Replace image if new uploaded
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

                $oldPath = public_path('uploads/image/'.$popup->mobile_image);

                if(file_exists($oldPath)){
                    unlink($oldPath);
                }
            }
        }

        $validated['frequency'] = $validated['popup_type'];
        $validated['active_department_id']      = $this->activeDeptId ?? NULL;
        $validated['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $validated['active_role_id']            = $this->activeRoleId ?? NULL;
        $popup->update($validated);
        DepartmentActivityLogger::log(
            'update',
            'dashboardpopup',
            $popup->id,
            $popup->name,
            $oldData,
            $popup->fresh()->toArray(),
            "Dashboard Popup '{$popup->name}' Updated Successfully."
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Dashboard Popup updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        $popup = DashboardPopup::findOrFail($id);

        if ($popup->desktop_image && file_exists(public_path('uploads/image/' . $popup->desktop_image))) {
            unlink(public_path('uploads/image/' . $popup->desktop_image));
        }

        if ($popup->mobile_image && file_exists(public_path('uploads/image/' . $popup->mobile_image))) {
            unlink(public_path('uploads/image/' . $popup->mobile_image));
        }
        DepartmentActivityLogger::log(
            'delete',
            'dashboardpopup',
            $popup->id,
            $popup->name,
            $popup->toArray(),
            [],
            "Dashboard Popup '{$popup->name}' Delete Successfully."
        );
        AdminLogger::log('delete', DashboardPopup::class, $id);
        $popup->delete();
        return response()->json(['status' => 'success', 'message' => 'Popup Delete Successfully']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:dashboard_popups,id',
        ]);

        $maxOrder = DashboardPopup::max('order');
        $currentOrder = $maxOrder;

        foreach ($request->order as $row) {

            $popup = DashboardPopup::find($row['id']);

            if ($popup) {

                $oldValues = $popup->toArray();

                $popup->update([
                    'order' => $currentOrder
                ]);

                $newValues = $popup->fresh()->toArray();

                // ✅ Correct logging
                DepartmentActivityLogger::log(
                    'reorder',
                    'dashboardpopup',
                    $popup->id,
                    $popup->name,
                    $oldValues,
                    $newValues,
                    "Dashboard Popup '{$popup->name}' reordered to position {$currentOrder}."
                );

                $currentOrder--;
            }
        }

        return response()->json(['status' => 'success']);
    }



}
