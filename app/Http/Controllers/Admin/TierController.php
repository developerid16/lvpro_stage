<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Models\Tier;
use App\Models\TierInterestGroup;
use App\Models\TierMemberType;
use App\Models\API\MemberBasicDetailIG;
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
            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}' title='Edit'>
                                <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                            </a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}' title='Delete'>
                                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                            </a>";
            }
            $action .= "</div>";

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
     * Returns distinct InterestGroupMainName list
     */
    public function getMainGroups()
    {
        $groups = MemberBasicDetailIG::select('InterestGroupMainName')
            ->distinct()
            ->orderBy('InterestGroupMainName')
            ->pluck('InterestGroupMainName');

        return response()->json(['status' => 'success', 'data' => $groups]);
    }

    /**
     * GET /admin/tiers/get-sub-groups?main_name=XXX
     * Returns distinct InterestGroupName for given main name
     */
    public function getSubGroups(Request $request)
    {
        $main = $request->get('main_name');

        $subGroups = MemberBasicDetailIG::select('InterestGroupName')
            ->where('InterestGroupMainName', $main)
            ->distinct()
            ->orderBy('InterestGroupName')
            ->pluck('InterestGroupName');

        return response()->json(['status' => 'success', 'data' => $subGroups]);
    }

    /**
     * GET /admin/tiers/get-member-types
     * Returns distinct MembershipTypeCode list
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
            'code'      => ['required', 'regex:/^[A-Za-z0-9\-]+$/'],
            'status'    => 'required',
            'tier_name' => 'required',
        ], [
            'code.required'      => 'Code is required',
            'code.regex'         => 'Code may contain only letters, numbers and hyphens',
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
            $tier = Tier::create($validator->validated());

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
            'code'      => ['required', 'regex:/^[A-Za-z0-9\-]+$/'],
            'status'    => 'required',
            'tier_name' => 'required',
        ], [
            'code.required'      => 'Code is required',
            'code.regex'         => 'Code may contain only letters, numbers and hyphens',
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
            $tier->update($validator->validated());

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
            TierInterestGroup::where('tier_id', $id)->delete();
            TierMemberType::where('tier_id', $id)->delete();
            Tier::where('id', $id)->delete();

            AdminLogger::log('delete', Tier::class, $id);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Tier Deleted Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

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
}