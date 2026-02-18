<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MasterService;
use Illuminate\Http\Request;

class MasterAPIController extends Controller
{
    protected $masterService;
    public function __construct(MasterService $masterService)
    {
        $this->masterService = $masterService;
    }

    /**
     * get gender
     */
    public function gender(Request $request)
    {
        try {
            $records = $this->masterService
                ->getGender();

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

    /** marital status */
    public function maritalStatus(Request $request)
    {
        try {
            $records = $this->masterService
                ->getMaritalStatus();

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

    /** card type */
    public function cardType(Request $request)
    {
        try {
            $records = $this->masterService
                ->getCardType();

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

    /** dependent type */
    public function dependentType(Request $request)
    {
        try {
            $records = $this->masterService
                ->getDependentType();

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

    /** membership code */
    public function interestGroup(Request $request)
    {
        try {
            $records = $this->masterService
                ->getInterestGroup();

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

    /** zone */
    public function zone(Request $request)
    {
        try {
            $records = $this->masterService
                ->getZone();

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

    /** membership code */
    public function membershipCode(Request $request)
    {
        try {
            $records = $this->masterService
                ->getMembershipCode();

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

