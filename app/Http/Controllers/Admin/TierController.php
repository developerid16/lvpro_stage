<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Helpers\DepartmentActivityLogger;
use App\Models\Tier;
use App\Models\TierInterestGroup;
use App\Models\TierMemberType;
use App\Models\Master\MasterInterestGroup;
use App\Models\API\MemberBasicDetailsModified;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TierController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.tier.";
        $permission_prefix = $this->permission_prefix = 'tier';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Tier',
            'module_base_url'   => url('admin/tiers')
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

    // =====================================================================
    // INDEX
    // =====================================================================
    public function index(Request $request)
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    // =====================================================================
    // DATATABLE
    // =====================================================================
    public function datatable(Request $request)
    {
        $qb = Tier::with(['interestGroups', 'memberTypes']);
        // ✅ Super Admin = all records, Other users = only their own records
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('active_department_id', $this->activeDeptId);
            $qb->where('active_club_location_id', $this->activeLocationId);
            $qb->where('active_role_id', $this->activeRoleId);
        }
        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'code',
            'tier_name',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex       = $result['offset'] ?? 0;

        $final_data = [];
        $i          = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

            $createdAt = $row->created_at->format(config('safra.date-format'));
            $updatedAt = $row->updated_at->format(config('safra.date-format'));

            // Action buttons
            // $action = "<div class='d-flex gap-3'>";
            // if (Auth::user()->can($this->permission_prefix . '-edit')) {
            //     $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}' title='Edit'>
            //                     <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
            //                 </a>";
            // }
            // if (Auth::user()->can($this->permission_prefix . '-delete')) {
            //     $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}' title='Delete'>
            //                     <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            //                 </a>";
            // }
            // $action .= "</div>";
            $activePermissions = session('active_permissions', []);

            $canEdit   = in_array($this->permission_prefix . '-edit',   $activePermissions) || Auth::user()->hasRole('Super Admin');
            $canDelete = in_array($this->permission_prefix . '-delete', $activePermissions) || Auth::user()->hasRole('Super Admin');
            $canActivityLog = in_array($this->permission_prefix . '-activity-log', $activePermissions) || Auth::user()->hasRole('Super Admin');

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

            if ($canActivityLog) {
                $action .= "<a target='_blank' href='" . url('admin/tiers/' . $row->id . '/activity-log') . "' 
                                class='activity-log text-primary' 
                                data-id='$row->id'
                                title='Merchants Activity Log'>
                                <i class='mdi mdi-history action-icon font-size-18'></i>
                            </a>";
            }

            // Build IG badges
            $igTags = '';
            foreach ($row->interestGroups as $ig) {
                $igTags .= "<span class='badge bg-soft-primary text-primary me-1 mb-1'>{$ig->interest_group_main_name} / {$ig->interest_group_name}</span>";
            }

            // Build Member Type badges
            $mtTags = '';
            foreach ($row->memberTypes as $mt) {
                $mtTags .= "<span class='badge bg-soft-success text-success me-1 mb-1'>{$mt->membership_type_code}</span>";
            }

            $final_data[$i] = [
                'sr_no'           => $index,
                'tier_name'       => $row->tier_name,
                'code'            => $row->code,
                'interest_groups' => $igTags ?: '<span class="text-muted">-</span>',
                'member_types'    => $mtTags ?: '<span class="text-muted">-</span>',
                'status'          => $row->status == 'Active'
                    ? "<span class='badge badge-soft-success'>Active</span>"
                    : "<span class='badge badge-soft-danger'>Inactive</span>",
                'created_at'      => $createdAt,
                'updated_at'      => $updatedAt,
                'action'          => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }

    // =====================================================================
    // DROPDOWN APIs
    // =====================================================================

    /**
     * GET /admin/tiers/get-main-groups
     * Returns distinct { interest_group_main_id, interest_group_main_name } list.
     * The blade uses interest_group_main_id as the <option value> so that
     * getSubGroups() can filter by UUID (reliable) instead of by name string.
     */
    public function getMainGroups()
    {
        $groups = MasterInterestGroup::select('interest_group_main_id', 'interest_group_main_name')
            ->groupBy('interest_group_main_id', 'interest_group_main_name')
            ->orderBy('interest_group_main_name')
            ->get();

        return response()->json(['status' => 'success', 'data' => $groups]);
    }

    /**
     * GET /admin/tiers/get-sub-groups?interest_group_main_id=UUID
     *  OR /admin/tiers/get-sub-groups?interest_group_main_id[]=UUID1&interest_group_main_id[]=UUID2
     *
     * Returns { interest_group_main_name, interest_group_name } pairs
     * filtered by interest_group_main_id (UUID), so name-case differences
     * can never cause empty results.
     */
    public function getSubGroups(Request $request)
    {
        $mainIds = $request->get('interest_group_main_id');

        $query = MasterInterestGroup::select(
            'interest_group_main_name',
            'interest_group_name'
        );

        if (is_array($mainIds)) {
            $query->whereIn('interest_group_main_id', $mainIds);
        } else {
            $query->where('interest_group_main_id', $mainIds);
        }

        $data = $query
            ->distinct()
            ->orderBy('interest_group_main_name')
            ->orderBy('interest_group_name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * GET /admin/tiers/get-member-types
     * Returns distinct MembershipTypeCode list.
     */
    public function getMemberTypes()
    {
        $types = MemberBasicDetailsModified::select('MembershipTypeCode')
            ->whereNotNull('MembershipTypeCode')
            ->where('MembershipTypeCode', '!=', '')
            ->distinct()
            ->orderBy('MembershipTypeCode')
            ->pluck('MembershipTypeCode');

        return response()->json(['status' => 'success', 'data' => $types]);
    }

    // =====================================================================
    // STORE
    // =====================================================================
    public function store(Request $request)
    {
        // Step 1: Validate basic fields
        $validator = Validator::make($request->all(), [
            'code'      => 'required',
            'status'    => 'required',
            'tier_name' => 'required',
        ], [
            'code.required'      => 'Code is required',
            'status.required'    => 'Status is required',
            'tier_name.required' => 'Tier name is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: At least one IG OR one Member Type must be provided
        $hasIG = $request->filled('interest_groups')
                 && is_array($request->interest_groups)
                 && count(array_filter($request->interest_groups)) > 0;

        $hasMT = $request->filled('member_types')
                 && is_array($request->member_types)
                 && count(array_filter($request->member_types)) > 0;

        if (!$hasIG && !$hasMT) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'ig_or_mt' => ['Please add at least one Interest Group or one Member Type.']
                ]
            ], 422);
        }

        // Step 3: Save
        DB::beginTransaction();
        try {
            // $tier = Tier::create($validator->validated());
            $data = $validator->validated();
            $data['added_by'] = Auth::user()->id;
            $data['active_department_id']      = $this->activeDeptId ?? NULL;
            $data['active_club_location_id']   = $this->activeLocationId ?? NULL;
            $data['active_role_id']            = $this->activeRoleId ?? NULL;
            $tier = Tier::create($data);

            if ($hasIG) {
                foreach ($request->interest_groups as $ig) {
                    if (!empty($ig['main_name']) && !empty($ig['sub_name'])) {
                        TierInterestGroup::create([
                            'tier_id'                  => $tier->id,
                            'interest_group_main_name' => $ig['main_name'],
                            'interest_group_name'      => $ig['sub_name'],
                            'is_active'                => 1,
                        ]);
                    }
                }
            }

            if ($hasMT) {
                foreach ($request->member_types as $mt) {
                    if (!empty($mt)) {
                        TierMemberType::create([
                            'tier_id'              => $tier->id,
                            'membership_type_code' => $mt,
                            'is_active'            => 1,
                        ]);
                    }
                }
            }

            AdminLogger::log('create', Tier::class, $tier->id);
            DepartmentActivityLogger::log(
                'create',
                'tier',
                $tier->id,
                $tier->tier_name,
                [],
                $tier->toArray(),
                "Tier '{$tier->tier_name}' Created Successfully."
            );
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Tier Created Successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // =====================================================================
    // EDIT (load modal HTML)
    // =====================================================================
    public function edit($id)
    {
        $this->layout_data['data'] = Tier::with(['interestGroups', 'memberTypes'])->findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    // =====================================================================
    // UPDATE
    // =====================================================================
    public function update(Request $request, $id)
    {
        // Step 1: Validate basic fields
        $validator = Validator::make($request->all(), [
            'code'      => 'required',
            'status'    => 'required',
            'tier_name' => 'required',
        ], [
            'code.required'      => 'Code is required',
            'status.required'    => 'Status is required',
            'tier_name.required' => 'Tier name is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: At least one IG OR one Member Type must be provided
        $hasIG = $request->filled('interest_groups')
                 && is_array($request->interest_groups)
                 && count(array_filter($request->interest_groups)) > 0;

        $hasMT = $request->filled('member_types')
                 && is_array($request->member_types)
                 && count(array_filter($request->member_types)) > 0;

        if (!$hasIG && !$hasMT) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'ig_or_mt' => ['Please add at least one Interest Group or one Member Type.']
                ]
            ], 422);
        }

        // Step 3: Update
        DB::beginTransaction();
        try {
            $tier = Tier::findOrFail($id);
            $oldData = $tier->toArray();
            $data = array_merge(
                $validator->validated(),
                [
                    'active_department_id'    => $this->activeDeptId ?? null,
                    'active_club_location_id' => $this->activeLocationId ?? null,
                    'active_role_id'          => $this->activeRoleId ?? null,
                ]
            );

            $tier->update($data);
            $tierRecords = Tier::findOrFail($id);
            DepartmentActivityLogger::log(
                'update',
                'tier',
                $tierRecords->id,
                $tierRecords->tier_name,
                $oldData,
                $tierRecords->fresh()->toArray(),
                "Tier '{$tierRecords->tier_name}' Updated Successfully."
            );

            // Sync IGs: delete all old, insert new
            TierInterestGroup::where('tier_id', $id)->delete();
            if ($hasIG) {
                foreach ($request->interest_groups as $ig) {
                    if (!empty($ig['main_name']) && !empty($ig['sub_name'])) {
                        TierInterestGroup::create([
                            'tier_id'                  => $id,
                            'interest_group_main_name' => $ig['main_name'],
                            'interest_group_name'      => $ig['sub_name'],
                            'is_active'                => 1,
                        ]);
                    }
                }
            }

            // Sync Member Types: delete all old, insert new
            TierMemberType::where('tier_id', $id)->delete();
            if ($hasMT) {
                foreach ($request->member_types as $mt) {
                    if (!empty($mt)) {
                        TierMemberType::create([
                            'tier_id'              => $id,
                            'membership_type_code' => $mt,
                            'is_active'            => 1,
                        ]);
                    }
                }
            }
            AdminLogger::log('update', Tier::class, $id);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Tier Updated Successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // =====================================================================
    // DELETE
    // =====================================================================
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // TierInterestGroup::where('tier_id', $id)->delete();
            // TierMemberType::where('tier_id', $id)->delete();
            // Tier::where('id', $id)->delete();
            $tier = Tier::find($id);
            $tier->delete();
            DepartmentActivityLogger::log(
                'delete',
                'tier',
                $tier->id,
                $tier->tier_name,
                $tier->toArray(),
                [],
                "Tier '{$tier->tier_name}' moved to trash."
            );
            
            AdminLogger::log('delete', Tier::class, $id);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Tier Deleted Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // =====================================================================
    // MISC
    // =====================================================================
    public function milestoneSave(Request $request)
    {
        return redirect('admin/tiers');
    }

    public function create()
    {
        //
    }

    public function show(Tier $tier)
    {
        //
    }

    /* -----------------------------------------------------
     * TRASH AJAX
     * ----------------------------------------------------- */
    public function trash(Request $request)
    {
        if ($request->ajax()) {
            $qb = Tier::with(['interestGroups', 'memberTypes'])->onlyTrashed();
            $result = $this->get_sort_offset_limit_query($request, $qb, [
                'id',
                'code',
                'tier_name',
                'status',
                'created_at',
                'updated_at',
            ]);

            $rowsQueryBuilder = $result['data'];
            $startIndex       = $result['offset'] ?? 0;

            $final_data = [];
            $i          = 0;

            foreach ($rowsQueryBuilder->get() as $row) {
                $index = $startIndex + $i + 1;

                $createdAt = $row->created_at->format(config('safra.date-format'));
                $updatedAt = $row->updated_at->format(config('safra.date-format'));

                // Build IG badges
                $igTags = '';
                foreach ($row->interestGroups as $ig) {
                    $igTags .= "<span class='badge bg-soft-primary text-primary me-1 mb-1'>{$ig->interest_group_main_name} / {$ig->interest_group_name}</span>";
                }

                // Build Member Type badges
                $mtTags = '';
                foreach ($row->memberTypes as $mt) {
                    $mtTags .= "<span class='badge bg-soft-success text-success me-1 mb-1'>{$mt->membership_type_code}</span>";
                }

                $final_data[$i] = [
                    'sr_no'           => $index,
                    'tier_name'       => $row->tier_name,
                    'code'            => $row->code,
                    'interest_groups' => $igTags ?: '<span class="text-muted">-</span>',
                    'member_types'    => $mtTags ?: '<span class="text-muted">-</span>',
                    'status'          => $row->status == 'Active'
                        ? "<span class='badge badge-soft-success'>Active</span>"
                        : "<span class='badge badge-soft-danger'>Inactive</span>",
                    'created_at'      => $createdAt,
                    'updated_at'      => $updatedAt,
                    'action'          => "<div class='d-flex gap-3'>
                                        <a href='javascript:void(0)' class='restore_btn' data-id='{$row->id}'>
                                            <i class='mdi mdi-restore text-success action-icon font-size-18'></i>
                                        </a>
                                        <a href='javascript:void(0)' class='force_delete_btn' data-id='{$row->id}'>
                                            <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                                        </a>
                                    </div>",
                ];

                $i++;
            }

            return [
                'items' => $final_data,
                'count' => $result['count'] ?? $rowsQueryBuilder->count(),
            ];
        }
        return view($this->view_file_path . "trash")->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * RESTORE
     * ----------------------------------------------------- */
    public function restore($id)
    {
        // Tier::withTrashed()->findOrFail($id)->restore();
        $tier = Tier::withTrashed()->findOrFail($id);
        $tier->restore();
        DepartmentActivityLogger::log(
            'restore',
            'tier',
            $tier->id,
            $tier->tier_name,
            [],
            [],
            "Tier '{$tier->tier_name}' Restored Successfully."
        );
        return response()->json([
            'status'  => 'success',
            'message' => 'Tier Restored Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * FORCE DELETE
     * ----------------------------------------------------- */
    public function forceDelete($id)
    {
        TierInterestGroup::where('tier_id', $id)->delete();
        TierMemberType::where('tier_id', $id)->delete();
        $tier = Tier::withTrashed()->findOrFail($id);
        $tierName = $tier->tier_name;
        $tierData = $tier->toArray();
        $tier->forceDelete();
        // Tier::withTrashed()->findOrFail($id)->forceDelete();
        DepartmentActivityLogger::log(
            'force_delete',
            'tier',
            $id,
            $tierName,
            $tierData,
            [],
            "Tier '{$tierName}' permanently deleted."
        );
        return response()->json([
            'status'  => 'success',
            'message' => 'Tier Permanently Deleted'
        ]);
    }

    public function activityLog($record_id)
    {
        $logs = DB::table('department_activity_logs')
            ->where('record_id', $record_id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Summary — created by, last updated by, approved by
        $createdLog  = $logs->where('action', 'create')->first();
        $approvedLog = $logs->whereIn('action', ['approve'])->last();
        $rejectedLog = $logs->whereIn('action', ['reject'])->last();
        $updatedLogs = $logs->where('action', 'update');

        $this->layout_data['logs']        = $logs;
        $this->layout_data['record_id']   = $record_id;
        $this->layout_data['createdLog']  = $createdLog;
        $this->layout_data['approvedLog'] = $approvedLog;
        $this->layout_data['rejectedLog'] = $rejectedLog;
        $this->layout_data['updatedLogs'] = $updatedLogs;

        return view("admin.activity-log")->with($this->layout_data);
    }
}