<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetMemberByToken;
use App\Services\GUIDService;
use Illuminate\Support\Facades\Config;

class GUIDController extends Controller
{
    protected $guidService;
    private $limit;
    private $last_modified;
    public function __construct(GUIDService $guidService)
    {
        $this->guidService = $guidService;
        $this->last_modified = Config::get('safra.last_modified');
        $this->limit = Config::get('safra.limit');
    }

    /**
     * Summary of createPaymentReceipt
     */
    public function getmemberidtoken(GetMemberByToken $request)
    {
        try {
            $records = $this->guidService->memberidByToken($request->validated());
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
