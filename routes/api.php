<?php

use App\Http\Controllers\API\MasterAPIController;
use App\Http\Controllers\API\MemberInformationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/ping', function (Request $request) {
    return "pong";
});


// test route for member information api
Route::prefix('memberInformation')->group(function () {
    Route::get('basic-details-info-modified', [MemberInformationController::class, 'GetBasicDetailInfoByModified']);
    Route::get('ig-basic-details', [MemberInformationController::class, 'GetBasicDetailIg']);
    Route::get('member-latest-transaction', [MemberInformationController::class, 'GetLatestTransaction']);
    Route::get('member-zip-code', [MemberInformationController::class, 'GetCustomerZone']);
});

// MASTER API START
Route::prefix('master')->group(function () {
    Route::get('gender', [MasterAPIController::class, 'gender']);
    Route::get('interest-group', [MasterAPIController::class, 'interestGroup']);
    Route::get('card-type', [MasterAPIController::class, 'cardType']);
    Route::get('dependent-type', [MasterAPIController::class, 'dependentType']);
    Route::get('membership-code', [MasterAPIController::class, 'membershipCode']);
    Route::get('marital-status', [MasterAPIController::class, 'maritalStatus']);
    Route::get('zone', [MasterAPIController::class, 'zone']);
});
// MASTER API END

// ax api
Route::prefix('ax')->group(function () {
    Route::get('basic-detail-information-method', [MemberInformationController::class, 'infoByMethod']);
    Route::get('get-shopping-cart-no', [MemberInformationController::class, 'getShoppingCart']);
    Route::get('clear-shopping-cart', [MemberInformationController::class, 'clearShoppingCart']);
   
    Route::get('add-merchandise-item-cart', [MemberInformationController::class, 'addMerchandiseItemToCart']);
});

Route::get('master/schedule-run', function () {
    Artisan::call('schedule:run');
    return response()->json([
        'status'  => 'success',
        'message' => 'Schedule executed',
        'output'  => Artisan::output()
    ]);
});