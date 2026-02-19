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

class MemberInformationController extends Controller
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
    public function GetBasicDetailInfoByModified(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->safraServiceAPI
                ->basicDetailInfoModified($lastModified, $limit);

            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
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
     * Summary of get basic detail info by ig
     */
    public function GetBasicDetailIg(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->safraServiceAPI
                ->getIGbasicdetail($lastModified, $limit);

            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
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
     * Summary of GetLatestTransaction
     */
    public function GetLatestTransaction(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->safraServiceAPI
                ->getLatestTransaction($lastModified, $limit);
            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
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
     * Summary of GetCustomerZone
     */
    public function GetCustomerZone(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->safraServiceAPI
                ->getCustomerZone($lastModified, $limit);
            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
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
     * Summary of infoByMethod
     */
    public function infoByMethod(InfoByMethod $request)
    {
        try {
            $records = $this->safraServiceAPI->getInfoByMethod( $request->validated());
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

    /** get shopping cart */
    public function getShoppingCart(GetShoppingCard $request)
    {
        $memberid = $request->member_id ?? 'A100063879';
        if (empty($memberid)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member ID is required'
            ], 400);
        }
        try {
            $lastModified = $request->last_modified ?? '2025-09-17';
            $records = $this->safraServiceAPI->getShoppingCartNo($request->validated());
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
     * Summary of clearShoppingCart
     */
    public function clearShoppingCart(ClearShoppingCart $request)
    {
        try {
            $records = $this->safraServiceAPI->clearShoppingCart($request->validated());
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
     * add-merchandise-itemCart
     */
    public function addMerchandiseItemCart(AddMerchandiseItemCartRequest $request)
    {
        try {
            $records = $this->safraServiceAPI
                ->addMerchandiseItemCart($request->validated());

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
     * Summary of addPaymentMethod
     */
    public function addPaymentMethod(AddPaymentMethodRequest $request)
    {
        try {
            $records = $this->safraServiceAPI->addPaymentMethod($request->validated());
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
     * Summary of createPaymentReceipt
     */
    public function createPaymentReceipt(CreatepaymentReceipt $request)
    {
        try {
            $records = $this->safraServiceAPI->createPaymentReceipt($request->validated());
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
