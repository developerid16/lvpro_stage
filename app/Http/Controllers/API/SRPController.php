<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddMerchandiseItemCartRequest;
use App\Http\Requests\AddPaymentMethodRequest;
use App\Http\Requests\ClearShoppingCart;
use App\Http\Requests\CreatepaymentReceipt;
use App\Http\Requests\GetShoppingCard;
use App\Http\Requests\InfoByMethod;
use App\Services\SafraServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SRPController extends Controller
{
    protected $safraServiceAPI;
    private $limit;
    private $last_modified;
    public function __construct(SafraServiceAPI $safraServiceAPI)
    {
        $this->safraServiceAPI = $safraServiceAPI;
        $this->last_modified = Config::get('safra.last_modified');
        $this->limit = Config::get('safra.limit');
    }

    /**
     * Summary of GetBasicDetailInfoByModified
     */
    public function masterListParameter(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->safraServiceAPI
                ->getSRPMasterListParameter();

            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of merchandiseItemList
     */ 
    public function merchandiseItemList(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->safraServiceAPI
                ->getMerchandiseItemList($lastModified, $limit);

            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
