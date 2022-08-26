<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Auth\UserAuthController;
use App\Http\Controllers\Api\v1\PanicController;



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



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controller\Api\v1'], function (){
//     Route::apiResource('panic', PanicController::class);
//     Route::apiResource('respone', ResponseController::class);

// })->middleware('auth:api');

Route::post('/login', [UserAuthController::class, 'login']);

Route::post('/panic/create', [PanicController::class, 'create'])->middleware('auth:api');
Route::post('/panic/cancel', [PanicController::class, 'cancel'])->middleware('auth:api');
Route::get('/panic/history', [PanicController::class, 'index'])->middleware('auth:api');


// Route::post('/login', 'Api\v1\Auth\UserAuthController@login');

