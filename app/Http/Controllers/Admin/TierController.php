<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TierController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.tier.";
        $permission_prefix = $this->permission_prefix = 'tiers';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Tier',
            'module_base_url' => url('admin/tiers')
        ];       
    }
  

    public function index(Request $request)
    {
       return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $qb = Tier::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'code',
            'tier_name',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

            $createdAt =  $row->created_at->format(config('safra.date-format'));
            $updatedAt =  $row->updated_at->format(config('safra.date-format'));

            // ACTION BUTTONS
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

            $final_data[$i] = [
                'sr_no'      => $index,
                'tier_name'  => $row->tier_name,
                'code'       => $row->code,
                'status'     => $row->status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
                'action'     => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        $post_data = $validator->validated();

        Tier::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Tier Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tier $tier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
         //
        $this->layout_data['data'] = Tier::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
         //
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

        $post_data = $validator->validated();
        Tier::find($id)->update($post_data);
        return response()->json(['status' => 'success', 'message' => 'Tier Update Successfully']);
    }
    function milestoneSave(Request $request)
    {         
        return redirect('admin/tiers');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
         Tier::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Tier Delete Successfully']);
    }
}
