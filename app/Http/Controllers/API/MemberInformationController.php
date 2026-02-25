<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddMerchandiseItemCartRequest;
use App\Http\Requests\AddPaymentMethodRequest;
use App\Http\Requests\ClearShoppingCart;
use App\Http\Requests\CreatepaymentReceipt;
use App\Http\Requests\GetShoppingCard;
use App\Http\Requests\InfoByMethod;
use App\Services\SafraAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MemberInformationController extends Controller
{
    protected $safraAPIService;
    private $limit;
    private $last_modified;
    public function __construct(SafraAPIService $safraAPIService)
    {
        $this->SafraAPIService = $safraAPIService;
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
            $records = $this->SafraAPIService
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
            $records = $this->SafraAPIService
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
            $records = $this->SafraAPIService
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
            $records = $this->SafraAPIService
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
            $records = $this->SafraAPIService->getInfoByMethod( $request->validated());
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
            $records = $this->SafraAPIService->getShoppingCartNo($request->validated());
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
            $records = $this->SafraAPIService->clearShoppingCart($request->validated());
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
            $records = $this->SafraAPIService
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
            $records = $this->SafraAPIService->addPaymentMethod($request->validated());
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
            $records = $this->SafraAPIService->createPaymentReceipt($request->validated());
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
