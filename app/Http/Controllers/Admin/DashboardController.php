<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

class DashboardController extends Controller
{

    public function dashboard(Request $req)
    {
        return view('admin.dashboard', get_defined_vars());
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile.userprofile', get_defined_vars());
    }
    public function general(Request $req)
    {
        $user = Auth::user();
        $req->validate([
            'email' => 'required|email',
            'name' => 'required',
        ]);

        $user->name = $req->name;
        $user->email = $req->email;
        $user->ghl_api_key = $req->ghl_api_key;
        if ($req->photo) {
            $user->photo = uploadFile($req->photo, 'uploads/profile', $req->name);
        }
        $user->save();
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function password(Request $req)
    {
        $user = Auth::user();
        $req->validate([
            'current_password' => 'required|password',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password'
        ]);
        $user->password = bcrypt($req->password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully!');
    }



    //this is for the handshakes stuff

    public function authCheck()
    {

        return view('auth-check');
    }

    public function saveWorkflow(Request $req)
    {
         try{
            $req->token = decrypt( $req->token) ;
        }catch(\Exception $e){
            return 'unsaved or refresh the page';
        }
        $user = User::find($req->token);
        if ($user) {
            save_setting('workflow_selected', $req->workflow_id, $user->id);
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
        $user = User::find($req->token);
        $res = new \stdClass;

        if ($user) {
            request()->user_id=$user->id;
            request()->location=$user->location;
            $planning = get_setting($user->id, 'planning_access_token');

            if ($planning) {
                $workflows = planning_api_call('people/v2/workflows', 'get', '', [], false, $planning);



                $res->organization_id = get_setting($user->id, 'planning_organization_id');
                $res->organization_name = get_setting($user->id, 'planning_organization_name');



                $res->workflow_selected = get_setting($user->id, 'workflow_selected');

                //$workflows= json_decode($workflows);
                $res->workflows = $workflows;
            }
        }
        return response()->json($res);
    }

    public function disconnectplanning(Request $req)
    {
        try{
            $req->token = decrypt( $req->token) ;
        }catch(\Exception $e){
            return 'unsaved or refresh the page';
        }
        $user = User::find($req->token);
        if ($user) {
            $ui = $user->id;
            save_setting('planning_access_token', null, $ui);
            save_setting('planning_refresh_token', null,  $ui);
            save_setting('planning_organization_id', null, $ui);
            return 'saved';
        }
        return 'unsaved';
    }

    public function handleAuth($req,$res,$locurl){
         $client = new Client(['http_errors' => false]);
                    $headers = [
                        'Authorization' => 'Bearer ' . $req->token
                    ];
                    $request = new Psr7Request('POST',   $locurl, $headers);
                    $res1 = $client->sendAsync($request)->wait();
                    $red =  $res1->getBody()->getContents();
                    $red = json_decode($red);


                    if ($red && property_exists($red, 'redirectUrl')) {
                        // @file_get_contents($red->redirectUrl);
                        $url = $red->redirectUrl;
                        $parts = parse_url($url);
                        parse_str($parts['query'], $query);
                        $code = $query['code'];

                        $res->crm_connected  = ghl_token($code, '1', 'eee');


                    }
                    return $res;
    }

    public function authChecking(Request $req)
    {
        if ($req->ajax()) {
            //save_logs(json_encode($req->all()));
            if ($req->has('location') && $req->has('token')) {
                $location = $req->location;
                $user = User::where('location', $req->location)->first();
                if (!$user) {
                    // aapi call
                    $user = new User();
                    $user->name = 'Test User';
                    $user->email = $location . '@gmail.com';
                    $user->password = bcrypt('shada2e3ewdacaeedd233edaf');
                    $user->location = $location;
                    $user->ghl_api_key = $req->token;
                    $user->role = 1;
                    $user->save();

                }
                $user->ghl_api_key = $req->token;
                $user->save();

                session(['location_id' => $user->id]);
                request()->user_id = $user->id;
                request()->location_id = $user->location;
                session()->put('uid', $user->id);

                // $planning_client_id=get_setting('1','planning_client_id');
                // $crm_client_id=get_setting('1','crm_client_id');


                $res = new \stdClass;
                $res->jwt= encrypt($user->id);
                $res->user_id = $user->id;
                $res->location_id = $user->location ?? null;
                   $res->is_crm = false;
                $res->is_planning = false;
                $res->token = $user->ghl_api_key;
                $res->planning_href = "https://api.planningcenteronline.com/oauth/authorize?client_id=" . getAccessToken('planning_client_id') . "&redirect_uri=" . route('planningcenter.callback') . "?location_id=" . $res->user_id . "&response_type=code&scope=people";
                $callbackurl = route('crm.callback');
                $locurl = "https://services.msgsndr.com/oauth/authorize?location_id=" . $res->location_id . "&response_type=code&userType=Location&redirect_uri=" . $callbackurl . "&client_id=" . getAccessToken('crm_client_id') . "&scope=calendars.readonly campaigns.readonly contacts.write contacts.readonly locations.readonly calendars/events.readonly locations/customFields.readonly locations/customValues.write opportunities.readonly calendars/events.write opportunities.write users.readonly users.write locations/customFields.write";



                session()->put('is_login_res',$res);
                $ch = Setting::where('location_id', $user->id)->first();
                if ($ch) {
                    $token = get_setting($user->id, 'ghl_refresh_token');

                     if ($token) {

                    $res->crm_connected = ghl_token($token, '1', 'eee');
                    if(!$res->crm_connected){
                        $res->crm_connected  = ConnectOauth($res->location_id,$req->token);
                    }


                } else {


                     $res->crm_connected  = ConnectOauth($res->location_id,$req->token);
                }

                   $res->is_crm = $res->crm_connected;


                    $planning = get_setting($user->id, 'planning_access_token');

                    if (!empty($planning)) {

                        $workflows = planning_api_call('people/v2/workflows', 'get', '', [], false, $planning);
                       if($user->id==105){
                        //dd($workflows);
                    }
                        $res->organization_id = get_setting($user->id, 'planning_organization_id');
                        $res->organization_name = get_setting($user->id, 'planning_organization_name');

                        $res->workflow_selected = get_setting($user->id, 'workflow_selected');
                        //$workflows= json_decode($workflows);
                        $res->workflows = $workflows;
                        $res->is_planning = true;
                    }
                }




                // return response()->json($res);

                // Auth::loginUsingId($user->id);
                // abort(redirect()->route('auth.check'));

                return response()->json($res);
            }

            return;
        }
        return;
    }
}
