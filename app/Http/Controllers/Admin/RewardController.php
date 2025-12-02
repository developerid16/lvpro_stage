<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentManagement;
use App\Models\Location;
use App\Models\PartnerCompany;
use App\Models\Reward;
use App\Models\RewardDates;
use App\Models\Tier;
use App\Models\UserPurchasedReward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RewardController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.reward.";
        $permission_prefix    = $this->permission_prefix    = 'reward';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Reward',
            'module_base_url'   => url('admin/reward'),
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

        $type = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $this->layout_data['type'] = $type;
        $this->layout_data['companies'] = PartnerCompany::where('status', 'Active')->get();
        $this->layout_data['tiers'] = Tier::all();



        return view($this->view_file_path . "index")->with($this->layout_data);
    }
    /**
     * Display a listing of the resource.
     */
    public function updateAutomatedReward(Request $request)
    {


        $data = ContentManagement::whereIn('name', [
            "birthday_reward_limit",
            "birthday_reward_voucher",
            "birthday_reward_group",
            "birthday_reward_email_noti",
            "birthday_reward_push_noti",
            "birthday_reward_sms_noti",
            "welcome_reward_limit",
            "welcome_reward_voucher",
            "welcome_reward_group",
            "welcome_reward_email_noti",
            "welcome_reward_push_noti",
            "welcome_reward_sms_noti",
        ])->get();
        foreach ($data as $d) {
            $keyData  = $d['name'];
            $d->value = $request->$keyData ?? '';
            $d->save();
        }
        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function indexAutomatedReward(Request $request)
    {

        $this->layout_data['data'] = ContentManagement::whereIn('name', [

            "birthday_reward_limit",
            "birthday_reward_voucher",
            "birthday_reward_group",
            "birthday_reward_email_noti",
            "birthday_reward_push_noti",
            "birthday_reward_sms_noti",
            "welcome_reward_limit",
            "welcome_reward_voucher",
            "welcome_reward_group",
            "welcome_reward_email_noti",
            "welcome_reward_push_noti",
            "welcome_reward_sms_noti",
        ])->pluck('value', 'name');
        $this->layout_data['data']['welcome_reward_group'] = json_decode($this->layout_data['data']['welcome_reward_group'] ?: '[]', true);

        $this->layout_data['data']['birthday_reward_group'] = json_decode($this->layout_data['data']['birthday_reward_group']  ?: '[]', true);
        $this->layout_data['rewards'] = Reward::all();
        return view($this->view_file_path . "automated-index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $type  = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $query = Reward::where('parent_type', $type);

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['no_of_keys'] = number_format($row->no_of_keys);

            $final_data[$key]['quantity']       = number_format($row->quantity);
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);

            $final_data[$key]['balance'] = $row->quantity == 0 ? 'Unlimited Stock' : number_format($row->quantity - $row->total_redeemed);

            $final_data[$key]['redeemed'] = number_format(UserPurchasedReward::where([['status', 'Redeemed'], ['reward_id', $row->id]])->count());

            $duration = $row->start_date->format(config('shilla.date-format'));

            $final_data[$key]['image'] = "<a href='" . asset("images/$row->image_1") . "' data-lightbox='set-$row->id'> <img src='" . asset("images/$row->image_1") . "' class='avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle' data-lightbox='lightbox' alt='img'></a>";

            if ($row->end_date) {
                $duration .= ' to ' . $row->end_date->format(config('shilla.date-format'));
            } else {
                $duration .= " - No Expiry";
            }
            $final_data[$key]['duration']   = $duration;
            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));

            $final_data[$key]['status'] = $row->status;

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            // if ($type === 'campaign-voucher') {
            $url = url("admin/campaign-voucher-assign/$row->id");
            $action .= "<a href='$url' title='Assign voucher to users.' ><i class='mdi mdi-card text-info action-icon font-size-18'></i></a>";
            // }
            $final_data[$key]['action'] = $action . "</div>";
        }
        $data          = [];
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
        $exd       = ! isset($request->end_date) ? 1 : 0;
        $ed        = $request->start_date ? Carbon::createFromFormat('Y-m-d', $request->start_date)->format(config('shilla.date-format')) : '';
        $post_data = $this->validate($request, [
            'name'         => 'required|max:191',
            'code'         => 'required|max:191|unique:rewards,code',
            'description'  => 'required|max:500',
            // 'term_of_use'  => 'required',
            // 'how_to_use' => 'required',
            // 'is_featured'  => 'required',
            // 'no_of_keys'   => 'required|numeric|min:0',
            // 'location_ids' => 'required|array|min:1',
            // 'location_ids.*' => 'exists:locations,id',
            // 'start_date'   => 'required|date|after_or_equal:' . date('Y-m-d'),
            // 'status'       => 'required',
            // 'image_1'      => 'required|image',
            'reward_type'  => 'required',
            // 'product_name' => 'required_if:reward_type,1',
            'amount'       => 'required_if:reward_type,0',
            'quantity'     => 'required|numeric|min:0',
            // 'company_id'   => 'required|exists:partner_companies,id',
            'end_date'     => 'required_if:expiry_day,0|date|after_or_equal:' . $request->start_date,
            // 'expiry_day'   => 'required|numeric|min:' . $exd,
            'image_2'      => 'sometimes|required|image',
            'image_3'      => 'sometimes|image|required_if:is_featured,1',
            'labels'       => 'sometimes',
            'days'         => 'sometimes',
            'sku'          => 'sometimes',
            'parent_type'  => 'required',
            'end_time'     => 'sometimes|required_with:start_time',
            'countdown'    => 'sometimes',
            'start_time'   => 'sometimes|required_with:end_time',
        ], [
            'end_date.after_or_equal' => 'End date must be a date after ' . $ed,
            'end_date.required_if'    => 'End date must be a provided when the Purchases Expiry is set to 0',
        ]);

        if ($request->hasFile('image_1')) {
            $imageName = time() . rand() . '.' . $request->image_1->extension();
            $request->image_1->move(public_path('images'), $imageName);
            $post_data['image_1'] = $imageName;
        }
        if ($request->hasFile('image_2')) {
            $imageName = time() . rand() . '.' . $request->image_2->extension();
            $request->image_2->move(public_path('images'), $imageName);
            $post_data['image_2'] = $imageName;
        }
        if ($request->hasFile('image_3')) {
            $imageName = time() . rand() . '.' . $request->image_3->extension();
            $request->image_3->move(public_path('images'), $imageName);
            $post_data['image_3'] = $imageName;
        }
        if (! isset($post_data['countdown'])) {
            $post_data['countdown'] = null;
        }
        if (! isset($post_data['days']) || ! $post_data['days']) {
            $post_data['days'] = null;
        }
        // if (! $post_data['end_date']) {
        //     $post_data['end_date'] = null;
        // }
        // if (! $post_data['end_time']) {
        //     $post_data['end_time'] = null;
        // }
        // if (! $post_data['start_time']) {
        //     $post_data['start_time'] = null;
        // }
        $post_data['labels'] = [];

        if ($request->labels) {

            $labelsArr = json_decode($request->labels, true);
            foreach ($labelsArr as $key => $value) {
                $post_data['labels'][] = $value['value'];
            }
        }

        $reward = Reward::create($post_data);

        
        $locationsDigital = $request->input('locations_digital', []);
        
        foreach ($locationsDigital as $locId => $locData) {
            // only store when checkbox selected
            // if (empty($locData['selected'])) {
            //     continue;
            // }
            
            // validate per-location fields (customize rules as needed)
            $validator = Validator::make($locData, [
                'publish_start_date' => 'nullable|date',
                'publish_start_time' => 'nullable|date_format:H:i',
                'publish_end_date'   => 'nullable|date|after_or_equal:publish_start_date',
                'publish_end_time'   => 'nullable|date_format:H:i',
                'sales_start_date'   => 'nullable|date',
                'sales_start_time'   => 'nullable|date_format:H:i',
                'sales_end_date'     => 'nullable|date|after_or_equal:sales_start_date',
                'sales_end_time'     => 'nullable|date_format:H:i',
            ], [
                'publish_end_date.after_or_equal' => "Publish End Date must be after Publish Start Date for location {$locId}",
                'sales_end_date.after_or_equal' => "Sales End Date must be after Sales Start Date for location {$locId}",
            ]);
            
            if ($validator->fails()) {
                DB::rollBack();
                throw new ValidationException($validator);
            }
            
            // prepare data to insert
            $detailData = [
                'publish_start_date' => $locData['publish_start_date'] ?? null,
                'publish_start_time' => $locData['publish_start_time'] ?? null,
                'publish_end_date'   => $locData['publish_end_date'] ?? null,
                'publish_end_time'   => $locData['publish_end_time'] ?? null,
                'sales_start_date'   => $locData['sales_start_date'] ?? null,
                'sales_start_time'   => $locData['sales_start_time'] ?? null,
                'sales_end_date'     => $locData['sales_end_date'] ?? null,
                'sales_end_time'     => $locData['sales_end_time'] ?? null,
                
            ];

            // upsert into reward_location_details (avoids duplicate unique key errors)
            RewardDates::UpdateOrCreate(
                ['reward_id' => $reward->id, 'merchant_id' => (int)$locId],
                $detailData
            );
        }


        return response()->json(['status' => 'success', 'message' => 'Reward Created Successfully']);
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
        $this->layout_data['data'] = Reward::find($id);
        $data = $this->layout_data['data']; // <- fix: create local $data used below
        $this->layout_data['type'] = $data->parent_type;
        $this->layout_data['companies'] = PartnerCompany::where('status', 'Active')->get();

        // Get locations for the selected company if available
        if ($data->company_id) {
            $this->layout_data['locations'] = Location::where('company_id', $data->company_id)
                ->where('status', 'Active')->get();
        }

        $this->layout_data['tiers'] = Tier::all();

        $existingLocationsData = [];

        // build existingLocationsData keyed by location_id
        if (isset($data->id)) {
            $details = RewardDates::where('reward_id', $data->id)->get(); // your model name
            foreach ($details as $d) {
                $existingLocationsData[$d->location_id] = [
                    'publish_start_date' => $d->publish_start_date,
                    'publish_start_time' => $d->publish_start_time,
                    'publish_end_date'   => $d->publish_end_date,
                    'publish_end_time'   => $d->publish_end_time,
                    'sales_start_date'   => $d->sales_start_date,
                    'sales_start_time'   => $d->sales_start_time,
                    'sales_end_date'     => $d->sales_end_date,
                    'sales_end_time'     => $d->sales_end_time,
                    'selected'           => 1,
                ];
            }
        }

        $this->layout_data['existingLocationsData'] = $existingLocationsData;

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $exd = ! isset($request->end_date) ? 1 : 0;
        $ed  = $request->start_date ? Carbon::createFromFormat('Y-m-d', $request->start_date)->format(config('shilla.date-format')) : '';

        $post_data = $this->validate(
            $request,
            [
                'code'         => 'required|max:191|unique:rewards,code,' . $id,
                'name'         => 'required|max:191',
                'description'  => 'required|max:500',
                'no_of_keys'   => 'required|numeric|min:0',
                'quantity'     => 'required|numeric|min:0',
                'company_id'   => 'required|exists:partner_companies,id',
                'location_ids' => 'required|array|min:1',
                'location_ids.*' => 'exists:locations,id',
                'start_date'   => 'required|date',
                'end_date'     => 'required_if:expiry_day,0|date|after_or_equal:' . $request->start_date,
                'expiry_day'   => 'required|numeric|min:' . $exd,
                'reward_type'  => 'required',
                'product_name' => 'required_if:reward_type,1',
                'amount'       => 'required_if:reward_type,0',
                'image_1'      => 'sometimes|image',
                'image_2'      => 'sometimes|required|image',
                'status'       => 'required',
                'term_of_use'  => 'required',
                // 'how_to_use' => 'required',
                'is_featured'  => 'required',
                'labels'       => 'sometimes',
                'days'         => 'sometimes',
                'sku'          => 'sometimes',
                'end_time'     => 'sometimes|required_with:start_time',
                'countdown'    => 'sometimes',
                'start_time'   => 'sometimes|required_with:end_time',
            ],
            [
                'end_date.after_or_equal' => 'End date must be a date after ' . $ed,
                'end_date.required_if'    => 'End date must be a provided when the Purchases Expiry is set to 0',

            ]
        );

        $rd = Reward::find($id);
        if ($request->hasFile('image_1')) {
            $imageName = time() . rand() . '.' . $request->image_1->extension();
            $request->image_1->move(public_path('images'), $imageName);
            $post_data['image_1'] = $imageName;
            try {
                unlink(public_path("images/$rd->image_1"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        if ($request->hasFile('image_2')) {
            $imageName = time() . rand() . '.' . $request->image_2->extension();
            $request->image_2->move(public_path('images'), $imageName);
            $post_data['image_2'] = $imageName;
            try {
                unlink(public_path("images/$rd->image_2"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        if ($request->hasFile('image_3')) {
            $imageName = time() . rand() . '.' . $request->image_3->extension();
            $request->image_3->move(public_path('images'), $imageName);
            $post_data['image_3'] = $imageName;
            try {
                unlink(public_path("images/$rd->image_3"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        if (! isset($post_data['countdown'])) {
            $post_data['countdown'] = null;
        }
        if (! isset($post_data['days']) || ! $post_data['days']) {
            $post_data['days'] = null;
        }
        if (! $post_data['end_date']) {
            $post_data['end_date'] = null;
        }
        if (! $post_data['end_time']) {
            $post_data['end_time'] = null;
        }
        if (! $post_data['start_time']) {
            $post_data['start_time'] = null;
        }
        $post_data['labels'] = [];

        if ($request->labels) {

            $labelsArr = json_decode($request->labels, true);
            foreach ($labelsArr as $key => $value) {
                $post_data['labels'][] = $value['value'];
            }
        }
        $rd->update($post_data);
        return response()->json(['status' => 'success', 'message' => 'Reward Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Reward::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Reward Delete Successfully']);
    }

    /**
     * Get locations by company ID
     */
    public function getLocationsByCompany(Request $request)
    {
        $companyId = $request->get('company_id');

        if (!$companyId) {
            return response()->json(['status' => 'error', 'message' => 'Company ID is required']);
        }



        $locations = Location::where('status', 'Active')
            ->select('id', 'name', 'code');

        if (is_array($companyId)) {
            $locations = $locations->whereIn('company_id', $companyId);
        } else {
            $locations = $locations->where('company_id', $companyId);
        }

        $locations = $locations->get();

        if ($request->type && $request->type == 'digital') {
            return response()->json(['status' => 'success', 'html' => view($this->view_file_path . 'location-checkbox-d', ['locations' => $locations])->render(), 'locations' => $locations]);
        }

        return response()->json(['status' => 'success', 'html' => view($this->view_file_path . 'location-checkbox-p', ['locations' => $locations])->render(), 'locations' => $locations]);
    }
}
