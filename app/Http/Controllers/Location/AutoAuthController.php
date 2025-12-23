<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Helpers\CRM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\churchmatrix\ServiceTimeJob;
use App\Jobs\churchmatrix\ManageCampusJob;

class AutoAuthController extends Controller
{

    protected const VIEW = 'autoauth';

    public function authChecking(Request $req)
    {
        if ($req->ajax()) {
            $t = $req->type ?? 'planning';
            if ($req->has('location') && $req->has('token')) {
                $location = $req->location;
// ->when($t == 'churchmatrix',function($q){
//                     $q->where('role',0);
//                 })
                $user = User::with('crmtoken')->where('location', $location)->first();

                if (!$user) {
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
                request()->merge(['user_id' => $user->id]);
                session([
                    'location_id' => $user->location,
                    'uid' => $user->id,
                    'user_id' => $user->id,
                    'user_loc' => $user->location,
                ]);

                $res = new \stdClass;
                $res->user_id = $user->id;
                $res->location_id = $user->location ?? null;
                $res->is_crm = false;
                request()->user_id = $user->id;
                $res->token = $user->ghl_api_key;
                $token = $user->crmtoken;
                $res->crm_connected = false;

                if ($token) {
                    list($tokenx, $token) = CRM::go_and_get_token($token->refresh_token, 'refresh', $user->id, $token);
                    $res->crm_connected = $tokenx && $token;
                }
                if (!$res->crm_connected) {
                    $res->crm_connected = CRM::ConnectOauth($req->location, $res->token, false, $user->id);
                }

                 //dd($res,$token);

                if ($res->crm_connected) {
                    if (Auth::check()) {
                        Auth::logout();
                        sleep(1);
                    }
                    Auth::login($user);
                }

                $res->is_crm = $res->crm_connected;
                $res->token_id = encrypt($res->user_id);

                $routes = [
                    'planning' => route('locations.planningcenter.index'),
                    'churchmatrix' => route('locations.churchmatrix.integration.events.index'),
                ];

                $res->route = @$routes[$t] ?? route('auth.planning');

                if($t != 'planning' && $user->role == 1)
                {
                    dispatch_sync((new ManageCampusJob($user)));
                    getCampusSession($user->id);
                }

                return response()->json($res);
            }
        }
        return response()->json(['status' => 'invalid request']);
    }


    public function connect(Request $request)
    {
        return view(self::VIEW . '.connect');
    }

    public function authError()
    {
        return view(self::VIEW . '.error');
    }
}
