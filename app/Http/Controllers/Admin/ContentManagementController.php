<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;

use Illuminate\Http\Request;
use App\Models\ContentManagement;
use App\Http\Controllers\Controller;

class ContentManagementController extends Controller
{
    //
    function __construct()
    {


        $this->view_file_path = "admin.content-management.";
        $permission_prefix = $this->permission_prefix = 'content-management';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'User',
            'module_base_url' => url('admin/content-management')
        ];

        $this->middleware("permission:$permission_prefix", ['only' => ['index', 'update']]);
        $this->middleware("permission:learn-more-page", ['only' => ['learnIndex', 'learnUpdate']]);
        $this->middleware("permission:referral-rate", ['only' => ['referralRateIndex', 'referralRateUpdate']]);
        $this->middleware("permission:app-content-management", ['only' => ['appIndex', 'appUpdate']]);
        $this->middleware("permission:app-management", ['only' => ['applicationManagement', 'applicationManagementUpdate']]);
    }
    public function index()
    {
        $this->layout_data['data'] = ContentManagement::whereIn('name', ['terms', 'pdpa', 'LegalPrivacyPolicy', 'milestone_reward_page', 'shilla_intro'])->pluck('value', 'name');

        return view($this->view_file_path . "index")->with($this->layout_data);
    }
    public function appIndex()
    {
        $this->layout_data['data'] = ContentManagement::whereIn('name', ['email', 'phone', 'operation_hours', 'location', 'facebook', 'instagram', 'xiaohongshu', 'WhatsApp'])->pluck('value', 'name');

        return view($this->view_file_path . "app-index")->with($this->layout_data);
    }
    public function applicationManagementSave(Request $request)
    {

         $this->validate($request, [
            'maintenance_icon' => 'sometimes|image|mimes:png',
        ]);


        $data =  ContentManagement::whereIn('name', ['maintenance_mode', 'maintenance_title', 'maintenance_descriptions'])->get();
        foreach ($data as $d) {
            $keyData = $d['name'];
            $d->value = $request->$keyData;
            $d->save();
        }

        if ($request->hasFile('maintenance_icon')) {
            $oldImage = ContentManagement::where('name', 'maintenance_icon')->first();
            $imageName = 'maintenance_icon.png';
            $request->maintenance_icon->move(public_path('images'), $imageName);
            $oldImage->value = $imageName;
            $oldImage->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function applicationManagement()
    {
        $this->layout_data['data'] = ContentManagement::whereIn('name', ['maintenance_mode', 'maintenance_icon', 'maintenance_title', 'maintenance_descriptions'])->pluck('value', 'name');

        return view($this->view_file_path . "app-management")->with($this->layout_data);
    }
    public function learnIndex()
    {
        $this->layout_data['content_data'] = ContentManagement::whereIn('name', ['evergreen_info', 'milestone_reward',])->pluck('value', 'name');
        $this->layout_data['tier_data']  = Tier::get(['detail', 'image', 'id', 'name']);
        return view($this->view_file_path . "learn-more")->with($this->layout_data);
    }
    public function referralRateIndex()
    {
        $this->layout_data['data'] = ContentManagement::whereIn('name', ['referral_to_keys', 'referral_by_keys',])->pluck('value', 'name');

        return view($this->view_file_path . "referral-rate")->with($this->layout_data);
    }
    public function notificationSettings()
    {
        $this->layout_data['data'] = ContentManagement::whereIn('name', ['email_aph_expiry_noti_day', 'email_reward_expiry_noti_day', 'email_keys_expiry_noti_day', 'keys_expiry_noti_day', 'reward_expiry_noti_day', 'aph_expiry_noti_day','email_aph_expiry_noti_day_two', 'email_reward_expiry_noti_day_two', 'email_keys_expiry_noti_day_two', 'keys_expiry_noti_day_two', 'reward_expiry_noti_day_two', 'aph_expiry_noti_day_two',
        'push_aph_expiry_noti_day',
        'push_keys_expiry_noti_day',
        'push_reward_expiry_noti_day',
        'push_aph_expiry_noti_day_two',
        'push_keys_expiry_noti_day_two',
        'push_reward_expiry_noti_day_two'
        ])->pluck('value', 'name');

        return view($this->view_file_path . "notification")->with($this->layout_data);
    }
    public function learnUpdate(Request $request)
    {

        $validateRule = [
            // 'evergreen_info' => 'required',
            'milestone_reward' => 'required',

        ];
        // $tier =  Tier::get(['detail', 'image', 'id', 'name']);

        // foreach ($tier as $value) {
        //     $validateRule["image_$value->id"] = 'sometimes|required|image';
        //     $validateRule["detail_$value->id"] = 'required|max:500';
        // }


        $post_data = $this->validate($request, $validateRule);
        // post_data
        $data =  ContentManagement::whereIn('name', [ 'milestone_reward'])->get();
        foreach ($data as $d) {
            $keyData = $d['name'];
            $d->value = $request->$keyData;
            $d->save();
        }

        // foreach ($tier as $t) {
        //     $detailKey = "detail_$t->id";
        //     $imageKey = "image_$t->id";
        //     $updateData['detail']  = $request->$detailKey;
        //     if ($request->hasFile($imageKey)) {

        //         $imageName = time() . rand() . '.' . $request->$imageKey->extension();
        //         $request->$imageKey->move(public_path('images'), $imageName);
        //         $updateData['image'] = $imageName;
        //         try {
        //             unlink(public_path("images/$t->image"));
        //         } catch (\Throwable $th) {
        //             //throw $th;
        //         }
        //     }
        //     $t->update($updateData);
        // }


        return redirect('admin/learn-more-page');
    }
    public function update(Request $request)
    {
        ContentManagement::where('name', $request->name)->update(['value' => $request->value]);
        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function appUpdate(Request $request)
    {


        $data =  ContentManagement::whereIn('name', ['email', 'phone', 'operation_hours', 'location', 'facebook', 'instagram', 'xiaohongshu', 'WhatsApp'])->get();
        foreach ($data as $d) {
            $keyData = $d['name'];
            $d->value = $request->$keyData;
            $d->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function referralRateUpdate(Request $request)
    {


        $data =  ContentManagement::whereIn('name', ['referral_to_keys', 'referral_by_keys'])->get();
        foreach ($data as $d) {
            $keyData = $d['name'];
            $d->value = $request->$keyData;
            $d->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function notificationSettingsUpdate(Request $request)
    {


        $data =  ContentManagement::whereIn('name', ['email_aph_expiry_noti_day', 'email_reward_expiry_noti_day', 'email_keys_expiry_noti_day', 'keys_expiry_noti_day', 'reward_expiry_noti_day', 'aph_expiry_noti_day','email_aph_expiry_noti_day_two', 'email_reward_expiry_noti_day_two', 'email_keys_expiry_noti_day_two', 'keys_expiry_noti_day_two', 'reward_expiry_noti_day_two', 'aph_expiry_noti_day_two',
        'push_aph_expiry_noti_day',
'push_keys_expiry_noti_day',
'push_reward_expiry_noti_day',
'push_aph_expiry_noti_day_two',
'push_keys_expiry_noti_day_two',
'push_reward_expiry_noti_day_two'
        
        ])->get();
        foreach ($data as $d) {
            $keyData = $d['name'];
            $d->value = $request->$keyData;
            $d->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }

    public function qrSettingsUpdate(Request $request)
    {


        $this->validate($request, [
            'QrImage' => 'sometimes|image|mimes:png',
        ]);


        $data =  ContentManagement::whereIn('name', ['dotsOptionsType', 'dotsOptionsColor', 'cornersSquareOptionsType', 'cornersSquareOptionsColor', 'backgroundOptionsColor', 'imageOptionsMargin', 'cornersDotOptionsType'])->get();
        foreach ($data as $d) {
            $keyData = $d['name'];
            $d->value = $request->$keyData;
            $d->save();
        }

        if ($request->hasFile('QrImage')) {
            $oldImage = ContentManagement::where('name', 'QrImage')->first();
            $imageName = 'qr.png';
            $request->QrImage->move(public_path('') . '/images', $imageName);
            $image  = $imageName;
            // try {
            //     unlink(public_path("images/$oldImage->value"));
            // } catch (\Throwable $th) {
            //     //throw $th;
            // }
            $oldImage->value = $image;
            $oldImage->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function qrSettings()
    {
        $this->layout_data['data'] = ContentManagement::whereIn('name', ['dotsOptionsType', 'dotsOptionsColor', 'cornersSquareOptionsType', 'cornersSquareOptionsColor', 'backgroundOptionsColor', 'imageOptionsMargin', 'cornersDotOptionsType', 'QrImage'])->pluck('value', 'name');

        return view($this->view_file_path . "qr")->with($this->layout_data);
    }
}
