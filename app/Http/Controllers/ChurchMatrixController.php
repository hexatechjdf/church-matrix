<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveChurchApiRequest;
use App\Services\ChurchService;
use Illuminate\Http\Request;
use App\Models\ChurchApi;
use App\Models\CrmToken;


class ChurchMatrixController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $settings = getChurchToken();
        $regions =  $settings ? $this->service->fetchRegions($settings) : null;

        return view('admin.church_matrix.index', compact('settings', 'regions'));
    }



    public function saveApi(SaveChurchApiRequest $request)
    {
        $settings = $this->service->saveApiCredentials($request->validated());
        $crm = getChurchToken();
        $regions =  $this->service->fetchRegions($crm) ?? null;

        return response()->json(['regions' => $regions,'seccess' => 'Connected successfully!']);
    }

    public function saveRegion(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
        ]);

        $this->service->saveChurchSetting($request->region_id,'company_id');

        return response()->json(['seccess' => 'Region saved successfully!']);
    }

    public function saveLocation(Request $request)
    {
        $request->validate([
            'location_id' => 'required|string',
        ]);

        $this->service->saveChurchSetting($request->location_id,'location_id');

        return response()->json(['seccess' => 'Location ID saved successfully!']);
    }

    public function saveTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string'
        ]);

        $this->service->saveChurchSetting($request->timezone,'timezone');

        return response()->json(['seccess' => 'Timezone updated successfully!']);
    }

    public function requestlisting(Request $request)
    {
        $tokens = CrmToken::whereHas('user',function($q){
            $q->where(['role'=>1, 'church_admin' => false,'crm_type' => 'church']);
        })->paginate(10);

        if ($request->ajax()) {
            return view('admin.components.crm-request-table', compact('tokens'))->render();
        }

    }

    public function acceptRequest(Request $request,$id)
    {
        $token = CrmToken::find($id);

        if(!$token){
            return response()->json(['message' => 'Token not found'], 404);
        }

        $user = $token->user;
        $user->church_admin = true;
        $user->save();

        return response()->json(['message' => 'Token accepted successfully']);

    }

    public function testRequest(Request $request,$id)
    {
        try{
            $crm = getChurchToken('location', $id);
            $res = $this->service->fetchRegions($crm);

            return response()->json(['message' => 'Tested successfully']);
        }catch(\Exception $e){

        }

        return response()->json(['message' => 'Unauthorized Data'], 404);
    }
}
