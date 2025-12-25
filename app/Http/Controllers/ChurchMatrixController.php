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
        $user = loginuser();
        $timezones = getTimeZones();
        $settings = getChurchToken();

        $regions =  $settings ? $this->service->fetchRegions($settings) : null;


        return view('admin.church_matrix.index', compact('settings', 'regions','timezones','user'));
    }



    public function saveApi(SaveChurchApiRequest $request)
    {
        $settings = $this->service->saveApiCredentials($request->validated());
        $crm = getChurchToken();
        $regions =  $this->service->fetchRegions($crm) ?? null;

        return response()->json(['regions' => $regions,'message' => 'Connected successfully!', 'success' => true]);
    }

    public function saveRegion(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
        ]);

        $this->service->saveChurchSetting($request->region_id,'company_id');

        return response()->json(['message' => 'Region saved successfully!', 'success' => true]);
    }

    public function saveLocation(Request $request)
    {
        $request->validate([
            'location_id' => 'required|string',
        ]);

        $user = loginUser();
        $user->location = $request->location_id;
        $user->church_admin = 1;
        $user->save();

        return response()->json(['message' => 'Location ID saved successfully!' , 'success' => true]);
    }

    public function saveTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string'
        ]);

        $user = loginUser();
        $user->timezone = $request->timezone;
        $user->save();

        // $this->service->saveChurchSetting($request->timezone,'timezone');

        return response()->json(['message' => 'Timezone updated successfully!' , 'success' => true]);
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
            return response()->json(['message' => 'Token not found', 'error' => true], 404);
        }

        $user = $token->user;
        $user->church_admin = true;
        $user->save();

        return response()->json(['message' => 'Token accepted successfully', 'success' => true]);

    }

    public function testRequest(Request $request,$id)
    {
        try{
            $crm = getChurchToken('location', $id);
            $res = $this->service->fetchRegions($crm);

            return response()->json(['message' => 'Tested successfully', 'success' => true]);
        }catch(\Exception $e){

        }

        return response()->json(['message' => 'Unauthorized Data','error' => true], 404);
    }
}
