<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\BroadcastMsg;
use App\Models\InAppNotiAll;
use App\Models\DeviceToken;
use App\Models\UnreadCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Mail\BroadcastEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;


class BroadcastController extends Controller
{

    function __construct()
    {

        $this->view_file_path = "admin.broadcast.";
        $permission_prefix = $this->permission_prefix = 'broadcast';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Broadcast Message',
            'module_base_url' => url('admin/broadcast')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $emails = AppUser::whereIn('status',['Active','Inactive'])->where('email_noti', 1)->get()->count();
        $phones = AppUser::whereIn('status',['Active','Inactive'])->where([['sms_noti', 1], ['country_code', '+65']])->get()->count();
        $users  = AppUser::where([['push_system_noti', 1]])->pluck('id');
        $tokens =  DeviceToken::whereIn('user_id', $users)->count();
        $this->layout_data['totaluser'] = "$emails  E-Mail and $phones SMS";
        $this->layout_data['total_email'] =  $emails;
        $this->layout_data['total_sms'] = $emails;
        $this->layout_data['total_noti'] = $tokens   ;

        
        $this->layout_data['issmsvalid'] = false;
        $this->layout_data['isemailvalid'] = false;
        $this->layout_data['isnotivalid'] = false;
        // $this->layout_data['phones'] = $phones;
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = BroadcastMsg::query();

        $query = $this->get_sort_offset_limit_query($request, $query, ['title',]);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['title'] = $row->title;
            $final_data[$key]['date'] = $row->date_of_publish->format(config('shilla.date-format') . ' H:i:s');

            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));
            $final_data[$key]['status'] = $row->status;
            $final_data[$key]['type'] = $row->type;



            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {

                if($row->type === "EDM"){

                //    $action .= "<a href='a' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
                }else{
                    
                    $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
                }

            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->is_type == "edm") {
            $post_data = $this->validate($request, [
                'title' => 'required',
 

            ]);
            $reqData = $request->all();

            $reqData['date_of_publish'] = Carbon::now()->addMinutes(2)->second(00);


            if ($request->hasFile('email_csv')) {
                $fileName = time() . rand() . '.' . $request->email_csv->getClientOriginalExtension();
                $request->email_csv->move(public_path('report'), $fileName);
                $reqData['csv_file'] = $fileName;
            }

            $data = [];
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . rand() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path() . '/images/attachments/', $fileName);
                    $data[] = $fileName;
                }
                $reqData['attachments'] = implode(', ', $data);
            }
            $reqData['type'] = "EDM";
            BroadcastMsg::create($reqData);

        } else {

            $post_data = $this->validate($request, [
                'title' => 'required',
                'date_of_publish' => 'required_without:send_now',


            ]);


            $reqData = $request->all();
            if ($request->has('send_now')) {
                $reqData['date_of_publish'] = Carbon::now()->addMinutes(2)->second(00);
            }

            if ($request->hasFile('email_csv')) {
                $fileName = time() . rand() . '.' . $request->email_csv->getClientOriginalExtension();
                $request->email_csv->move(public_path('report'), $fileName);
                $reqData['csv_file'] = $fileName;
            }

            $data = [];
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $fileName = time() . rand() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path() . '/images/attachments/', $fileName);
                    $data[] = $fileName;
                }
                $reqData['attachments'] = implode(', ', $data);
            }
            BroadcastMsg::create($reqData);
        }


        return response()->json(['status' => 'success', 'message' => 'Broadcast Created Successfully']);
    }
    public function broadcastTestingTemplate(Request $request)
    {



        $reqData = $request->all();
        $data = [];
        if ($request->hasfile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = time() . rand() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path() . '/images/attachments/', $fileName);
                $data[] = $fileName;
            }
        }


        $emails = explode(',', $reqData['emails']);
        foreach ($emails as $email) {
            $ed['email_content'] = $reqData['email_content'];
            $ed['unique_id'] = "S000001";
            $ed['en_email'] = Crypt::encryptString($email);

            Mail::to($email)->send(new BroadcastEmail($reqData['subject'], $ed, $data));
        }

        //BroadcastMsg::create($reqData);

        return response()->json(['status' => 'success', 'message' => 'Broadcast sent to user Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(BroadcastMsg $BroadcastMsg)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $emails = AppUser::whereIn('status',['Active','Inactive'])->where('email_noti', 1)->get()->count();
        $phones = AppUser::whereIn('status',['Active','Inactive'])->where([['sms_noti', 1], ['country_code', '+65']])->get()->count();
        $users  = AppUser::where([['push_system_noti', 1]])->pluck('id');
        $tokens =  DeviceToken::whereIn('user_id', $users)->count();
        $this->layout_data['totaluser'] = "$emails  E-Mail and $phones SMS";
        $this->layout_data['total_email'] =  $emails;
        $this->layout_data['total_sms'] = $emails;
        $this->layout_data['total_noti'] = $tokens;
        $value = BroadcastMsg::find($id);

        $this->layout_data['data'] = $value;
        
        $issmsvalid = $value->sms_content ? true : false;
        $isemailvalid = $value->email_subject && $value->email_content;
        $isnotivalid = $value->inapp_title && $value->inapp_content || $value->push_title && $value->push_subtitle;

        $this->layout_data['issmsvalid'] = $issmsvalid;
        $this->layout_data['isemailvalid'] = $isemailvalid;
        $this->layout_data['isnotivalid'] = $isnotivalid;

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $post_data = $this->validate($request, [
            'title' => 'required',
            'date_of_publish' => 'required_without:send_now',


        ]);
        $reqData = $request->all();
        if ($request->has('send_now')) {
            $reqData['date_of_publish'] = Carbon::now()->addMinutes(2)->second(00);
        }
        if ($request->hasFile('email_csv')) {
            $fileName = time() . rand() . '.' . $request->email_csv->getClientOriginalExtension();
            $request->email_csv->move(public_path('report'), $fileName);
            $reqData['csv_file'] = $fileName;
        }
        $data = [];
        if ($request->hasfile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = time() . rand() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path() . '/images/attachments/', $fileName);
                $data[] = $fileName;
            }
            $reqData['attachments'] = implode(', ', $data);
        }
        BroadcastMsg::find($id)->update($reqData);
        return response()->json(['status' => 'success', 'message' => 'Broadcast Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bm = BroadcastMsg::find($id);
        InAppNotiAll::where(
            'title',
            $bm->inapp_title,
        )->delete();
        $unreadCounts = UnreadCount::whereRaw('FIND_IN_SET(?, all_noti_id)', [$id])->get()->pluck('user_id');
        AppUser::whereNotIn('id', $unreadCounts)->where('noti_count', '>', 0)->decrement('noti_count');
        $bm->delete();
        return response()->json(['status' => 'success', 'message' => 'Broadcast Delete Successfully']);
    }
}