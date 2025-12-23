<?php

namespace App\Http\Controllers\Location\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PlanningService;
use App\Models\CrmToken;
use App\Jobs\Planning\SyncPlanningTokensJob;

class PlanningController extends Controller
{
    protected $planningService;


    public function __construct(PlanningService $planningService)
    {
        $this->planningService = $planningService;
    }

    public function index(Request $request)
    {
        return view('locations.planning.index');
    }

    public function callback(Request $request)
    {
        $code = $request->code;
        $loc_id = $request->location_id;
        list($status,$data) = $this->planningService->fetchPlanningToken($code,$loc_id,'code',false);
        if ($data && is_array($data) && property_exists($data, 'error')) {
            return redirect()->route('auth.check')->withError($data->error_description);
        }
        $this->planningService->setUserToken($loc_id,$data->access_token);
        $res_me = $this->planningService->planning_api_call('people/v2', 'get', '', [], false, $data->access_token);
        if($res_me && property_exists($res_me,'data')){
            $org_id = $res_me->data->id;
            $org_id_name='-';
            try{
                $org_id_name = $res_me->data->attributes->name;
            }catch(\Exception $e){

            }
            $crm = CrmToken::where('crm_type', 'planning')->where('company_id',$org_id)->where('user_id','<>',$loc_id)->first();

            if (!is_null($crm) && $crm) {
                $user = $crm->user;

                // echo 'Unable to connect Organization already connect with ';
                echo 'Unable to connect Organization already connect with '.$user->location;
                die;
            }
            $payload = [
                'access_token' => $data->access_token,
                'refresh_token' => $data->refresh_token,
                'company_id' => $org_id,
                'crm_type'=>'planning',
                'user_id'=>$loc_id,
                'organization_name' => $org_id_name,
            ];
            $this->planningService->saveToken($loc_id,$payload);
        }else{
            die('Unable to get organization id');
        }

         $webhooks = ['people.v2.events.email.created','people.v2.events.person.created','people.v2.events.phone_number.created'];
        try{
            foreach($webhooks as $t)
            {
            $obj = new \stdClass;
            $dataobj = new \stdClass;
            $dataobj->attributes = new \stdClass;
            $dataobj->attributes->name = $t;
            $dataobj->attributes->url = route('planning_lead_capture');
            $obj->data= $dataobj;
            $webhook = $this->planningService->planning_api_call('webhooks/v2/subscriptions', 'POST', json_encode($obj), [], false, $data->access_token);
            save_logs($webhook,'webhook');

            }
        }catch(\Exception $e){
            save_logs($e->getMessage(),'error');
        }

        return view('planning-done');
    }

    public function getContact()
    {
        $res = $this->planningService->planning_api_call('people/v2/emails', 'GET', '', [], true);
    }

    public function getPlanningSettings()
    {
        $user = loginUser();
        $res = new \stdClass;
        $res->is_planning = false;
        $c =  $user->planningToken ?? null;
        $res->planning_href = "https://api.planningcenteronline.com/oauth/authorize?client_id=" . getAccessToken('planning_client_id') . "&redirect_uri=" . route('planningcenter.callback') . "?location_id=" . $user->id . "&response_type=code&scope=people check_ins";
        $planning = @$c->access_token;
        if (!empty($planning)) {
            $workflows = $this->planningService->planning_api_call('people/v2/workflows', 'get', '', [], false, $planning);
            if($user->id==105){
            }

            $res->token = $user->ghl_api_key;
            $res->jwt= encrypt($user->id);
            $res->organization_id   = @$c->company_id;
            $res->organization_name = @$c->organization_name;
            $res->workflow_selected = $user->workflow_selected;
            $res->workflows = $workflows;
            $res->is_planning = true;
        }

        return response()->json($res);
    }

    public function eventtimes()
    {
        return 1;
    }

    public function saveWorkflow(Request $req)
    {
         try{
            $req->token = decrypt( $req->token) ;
        }catch(\Exception $e){
            return 'unsaved or refresh the page';
        }
        $user = loginUser($req->token);
        if ($user) {
            $user->workflow_selected = $req->workflow_id;
            $user->save();
            return 'saved';
        }
        return 'unsaved';
    }

    public function listworkflows(Request $req)
    {
        try{
            $req->token = decrypt( $req->token) ;
        }catch(\Exception $e){
            return 'unsaved or refresh the page';
        }
        $user = loginUser($req->token);
        $res = new \stdClass;

        if ($user) {
            request()->user_id=$user->id;
            request()->location=$user->location;
            $t = $user->planningToken ?? null;
            $planning = @$t->access_token;
            if ($planning) {
                $workflows = $this->planningService->planning_api_call('people/v2/workflows', 'get', '', [], false, $planning);
                $res->organization_id = $t->company_id;
                $res->organization_name = $t->organization_name;
                $res->workflow_selected = $user->workflow_selected;
                $res->workflows = $workflows;
            }
        }
        return response()->json($res);
    }

   public function disconnectplanning(Request $request)
    {
        try {
            $token = decrypt($request->token);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token or page refresh required'
            ], 400);
        }

        $user = loginUser($token);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->planningToken) {
            $user->planningToken->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Disconnected successfully'
        ]);
    }

    public function updateTokens(Request $request)
    {
        SyncPlanningTokensJob::dispatchSync(['refresh'=>1]);
    }


}


