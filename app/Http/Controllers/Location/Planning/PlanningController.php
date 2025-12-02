<?php

namespace App\Http\Controllers\Location\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\planningService;

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

    public function getPlanningSettings()
    {
        $user = loginUser();
        $res = new \stdClass;
        $res->is_planning = false;
        $res->planning_href = "https://api.planningcenteronline.com/oauth/authorize?client_id=" . getAccessToken('planning_client_id') . "&redirect_uri=" . route('planningcenter.callback') . "?location_id=" . $user->id . "&response_type=code&scope=people";
        $planning = get_setting($user->id, 'planning_access_token');
        if (!empty($planning)) {
            $workflows = planning_api_call('people/v2/workflows', 'get', '', [], false, $planning);
            if($user->id==105){
            }
            $res->organization_id = get_setting($user->id, 'planning_organization_id');
            $res->organization_name = get_setting($user->id, 'planning_organization_name');
            $res->workflow_selected = get_setting($user->id, 'workflow_selected');
            $res->workflows = $workflows;
            $res->is_planning = true;
        }

        return response()->json($res);
    }

    public function eventtimes()
    {
        return 1;
    }


}
