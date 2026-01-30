<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\APILogs;
use App\Models\RefundSale;
use App\Models\Sale;
use App\Models\VoucherLogs;
use Illuminate\Http\Request;

class APILogsController extends Controller
{

    //
    public function __construct()
    {

        $this->view_file_path = "admin.apilogs.";
        $permission_prefix = $this->permission_prefix = 'apilogs';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'API Logs',
            'module_base_url' => url('admin/apilogs')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create", ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view($this->view_file_path . "index")->with($this->layout_data);
    }
    /**
     * Display a listing of the resource.
     */
    public function indexTriggerd()
    {

        $this->layout_data['module_base_url'] = url('admin');

        return view($this->view_file_path . "index-triggerd")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = APILogs::query();
        $query = $this->get_sort_offset_limit_query($request, $query, []);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;

            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['req_data'] = json_encode($row->req_data);
            $final_data[$key]['status'] = $row->status;
         
            $final_data[$key]['request_id'] = $row->request_id;
            $final_data[$key]['response_data'] = json_encode($row->response_data);
            $final_data[$key]['start_time'] = $row->start_time->format(config('safra.date-format') . " g:i:s a");
            $final_data[$key]['end_time']="-";
            if($row->end_time){
            $final_data[$key]['end_time'] = $row->end_time->format(config('safra.date-format') . " g:i:s a");
        }
            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }
    public function datatableTriggerd(Request $request)
    {
        $query = APILogs::whereIn('name',['/api/redeem','/api/void']);
        $query = $this->get_sort_offset_limit_query($request, $query, []);

        $final_data = [];
        
        foreach ($query['data']->get() as $key => $row) {
            $temp['sr_no'] = $key + 1;

            $tempStatus = false;
            $temp['name'] = $row->name;
            $temp['req_data'] = json_encode($row->req_data);
            $temp['status'] = $row->status;
            if ($row->name === '/api/redeem') {
                $data = Sale::whereRaw('FIND_IN_SET(?, voucher_no)', [$row->req_data['Voucher_No']])->whereRaw('LENGTH(batch_id) > 5')->latest()->first();
              
                if ($data) {

                  
                    $itscall =  APILogs::where([['request_id', $data['batch_id']], ['id', '>', $row['id']]])->first();
                     if ($itscall) {
                        $temp['name'] .= "(2.1 Triggered)";
                    } else {
                        $temp['name'] .= "(2.1 Not  Triggered)";
                    $tempStatus = true;

                    }
                } else {
                    $temp['name'] .= "(2.1  Not Triggered)";
                    $tempStatus = true;

                }
            }
            if ($row->name === '/api/void') {
                $data =  RefundSale::whereRaw('FIND_IN_SET(?, voucher_no)', [$row->req_data['Voucher_No']])->whereRaw('LENGTH(batch_id) > 5')->latest()->first();
                if ($data) {
                    $itscall =     APILogs::where([['request_id', $data['batch_id']], ['id', '>', $row['id']]])->first();
                    if ($itscall) {

                        $temp['name'] .= "(2.2 Triggered)";
                    } else {
                        $temp['name'] .= "(2.2  Not Triggered)";
                    $tempStatus = true;

                    }
                } else {
                    $temp['name'] .= "(2.2  Not Triggered)";
                    $tempStatus = true;
                }
            }
            $temp['request_id'] = $row->request_id;
            $temp['response_data'] = json_encode($row->response_data);
            $temp['start_time'] = $row->start_time->format(config('safra.date-format') . " g:i:s a");
            $temp['end_time'] = $row->end_time->format(config('safra.date-format') . " g:i:s a");
            $temp['created_at'] = $row->created_at->format(config('safra.date-format'));
            if($tempStatus){
array_push($final_data,$temp);
            }
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = count( $final_data);
        return $data;
    }
    public function indexVoucher()
    {
         $this->layout_data['module_base_url'] = url('admin/voucherlogs');

        return view($this->view_file_path . "voucherlogs")->with($this->layout_data);
    }
 
    public function datatableVoucherLogs(Request $request)
    {
        $query = VoucherLogs::whereHas('reward')->with('reward');
        $query = $this->get_sort_offset_limit_query($request, $query, []);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[] = [
                'sr_no'       => $key + 1,
                'reward'      => $row->reward->name,
                'receipt_no'  => $row->receipt_no,
                'status'      => $row->action,
                // 'created_at'  => $row->created_at->format(config('safra.date-format').' g:i:s a'),
                'created_at'  => $row->created_at->format(config('safra.date-format')),
            ];
        }

        return [
            'items' => $final_data,
            'count' => $query['count'],
        ];
    }


}
