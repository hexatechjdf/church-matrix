<?php

use App\Http\Controllers\Api\FenceFtAvailableController;
use App\Http\Controllers\Api\FtAvailableController;
use App\Http\Controllers\Api\FenceController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\PlanningCenterController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('new-auth-connection',function(){
    $users = \App\Models\User::where('role',1)->get();
    //$users = [$users];
    $connected_locs = [];
    foreach($users as $u){
        
       // $token= explode('-',$u->ghl_api_key);
      //  unset($token[count($token)-1]);
       // $token = implode('-',$token);
        $token='2f52f7ee-bb38-4e4a-bc5c-0d1b922bbd56';
        $connected_locs[$u->location.'|'.$u->id] = ConnectOauth($u->location,$token,1);
        
    }
    dd($connected_locs);
});


// WebHook For CRM 
Route::post('crm-lead-capture', [PlanningCenterController::class, 'crm_lead_capture'])->name('crm_lead_capture');

Route::post('planning-lead-capture', [PlanningCenterController::class, 'planning_lead_capture'])->name('planning_lead_capture');
