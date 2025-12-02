<?php

use App\Models\Contact;
use App\Models\Locations;
use App\Models\Setting;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Client\Request;
use PhpParser\Node\Stmt\TryCatch;
use App\Helpers\gCache;

use Illuminate\Support\Facades\Cache;



function supersetting($key, $default = '', $keys_contain = null)
{
    Cache::forget($key);
    try {
        $setting = gCache::get($key, function () use ($default, $key, $keys_contain) {
            $setting = Setting::when($keys_contain, function ($q) use ($key, $keys_contain) {
                return $q->where('key', 'LIKE', $keys_contain)->pluck('value', 'key');
            }, function ($q) use ($key) {
                return $q->where(['key' => $key])->first();
            });

            $value = $keys_contain ? $setting : ($setting->value ?? $default);
            gCache::put($key, $value);
            return $value;
        });
        return $setting;
    } catch (\Exception $e) {
        return null;
    }

}

function loginUser()
{
    return auth()->user();
}

function login_id($id = "")
{
    if (!empty($id)) {
        return $id;
    }
    $id = auth()->user()->id;

    if (auth()->user()->role == 1) {
        return $id;
    }
    return $id;
}

function company_user($location = '', $key = 'location')
{
    if (!empty($location)) {
        return $loc = \App\Models\User::where($key, $location)->where('role', company_role())->first();
    } else if (\Auth::check()) {
        $user = auth()->user();
        if ($user->role == 1) {
            return $user->addedby;
        }
        return $user;
    }
}

function get_api_key()
{
    $cid = session('c_id');
}

function save_setting($key, $value = '', $id = null)
{

    $user =   $id ?? auth()->id();
    $check = Setting::where(['location_id' => $user, 'key' => $key])->first() ?? new Setting();
    if ($check && !$value) {
        Setting::where(['location_id' => $user, 'key' => $key])->delete();
    } else {
        $check->key = $key;
        $check->value = $value;
        $check->location_id = $user;
        $check->save();
    }
}



function get_setting($id, $type)
{
    $res = Setting::where(['location_id' => $id,  'key' => $type])->first();
    if ($res) {
        return $res->value;
    } else {
        return null;
    }
}

// getAccessToken
function getAccessToken($type)
{
    $res = Setting::where('key', $type)->first();
    return @$res->value;
}

// Planning Center Oauth Config
function get_planning_token($code, $type = "")
{
    $url = 'https://api.planningcenteronline.com/oauth/token';
    $headers['Content-Type'] = "application/json";
    $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);
    $options = [];
    $codekey=empty($type) ? "code" : "refresh_token";
    $data = [
        "grant_type" => empty($type) ? "authorization_code" : "refresh_token",
        $codekey => $code,
        "client_id" => getAccessToken('planning_client_id'),
        "client_secret" => getAccessToken('planning_client_sceret'),
        "redirect_uri" => route('planningcenter.callback') . "?location_id=" . request()->location_id
    ];
    $options['body'] = json_encode($data);
    $response = $client->request('POST', $url, $options);
    $bd = $response->getBody()->getContents();
    $bd = json_decode($bd);

    $resp = new \stdClass;
    $resp->url = $url;
    $resp->payload=$data;
    $resp->method='post';
    $resp->responseback = $bd;
    //$resp->tokenindb = Setting::where('key', 'planning_refresh_token')->where('location_id',request()->user_id)->first();
    //die(json_encode($resp));
    // if($bd && )

    return $bd;
}


// Planning Center Call
function planning_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $a_token = null)
{
    $type = 'planning_access_token';
    $baseurl = 'https://api.planningcenteronline.com/';

    $user_id  = request()->user_id;

    if (is_null($a_token)) {
        $bearer = get_setting($user_id, 'planning_access_token');
    } else {
        $bearer =  $a_token;
    }



    if(empty($bearer)){
       return '';
    }

    $location = get_setting($user_id, 'ghl_location_id');
    request()->location_id = $location;
    $headers['Authorization'] = 'Bearer '.$bearer;
    $headers['Content-Type'] = "application/json";
    $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);

    $options = [];
    if (!empty($data) && $method != 'get') {
        $options['body'] = $data;
    }
    $url1 = $baseurl . $url;
//     $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => $url1,
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 30,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => $method,
//   CURLOPT_POSTFIELDS =>$data,
//   CURLOPT_HTTPHEADER => array(
//     'Content-Type: application/json',
//     'Authorization: Bearer '.$bearer
//   ),
// ));

// $bd = curl_exec($curl);

// curl_close($curl);




    $response = $client->request($method, $url1, $options);
    $bd = $response->getBody()->getContents();
    $isaccessdenied=false;
    if(strpos($bd,'HTTP Basic: Access denied')!==false){
        $isaccessdenied=true;
    }
    if($user_id==105){
        //dd($bd);
    }
    $bd = json_decode($bd);


    //    dd($bd);



    if (($bd && property_exists($bd, 'errors') && is_array($bd->errors) && count($bd->errors)>0 && property_exists($bd->errors[0],'code') && strtolower($bd->errors[0]->code) == 'unauthorized') || $isaccessdenied) {
        $rfkey='planning_refresh_token';
        $refresh_token = get_setting($user_id, $rfkey);


        $lck=Cache::lock('planning_cache_lock_'.$user_id,40);
        $is_refresh=false;
        try{
            list($is_refresh,$a_token)= $lck->block(40, function () use ($user_id,$refresh_token,$rfkey) {
    $newrefresh_token = get_setting($user_id, $rfkey);
    if($newrefresh_token==''){
        return [false,''];
    }

    if($refresh_token!=$refresh_token){
        return [true,''];
    }
    $code = get_planning_token($refresh_token, $rfkey);



        if($code && property_exists($code,'access_token')){

            save_setting('planning_access_token', $code->access_token, $user_id);
            save_setting($rfkey, $code->refresh_token, $user_id);
            return [true,$code->access_token];

        }

        if($code && property_exists($code,'error_description') && $code->error_description=='The refresh token is no longer valid'){
             save_setting('planning_access_token', '', $user_id);
            save_setting($rfkey, '', $user_id);
            save_setting('planning_organization_id', '', $user_id);
         save_setting('planning_organization_name', '', $user_id);
        }
        return [false,''];
    });
        }catch(\Exception $e){

        }




        if($is_refresh){
            return planning_api_call($url , $method, $data , $headers , $json,$a_token);
        }


        if(session('is_login_res')){
            $res = session('is_login_res');
            session()->forget('is_login_res');
            response()->json($res)->send();
            die();

        }
        return '';

    }
    return $bd;
}


function get_default_settings($j, $k)
{
    return $k;
}

function save_logs($data,$key='planning')
{
    if (is_object($data) || is_array($data)) {
        $data = json_encode($data);
    }
    \DB::table('logs')->insert(['message' => $data,'name'=>$key]);
}

function api_call($url)
{
    // This one is for just fetching the extra attributes from the Planning center
    $type = 'planning_access_token';
    $bearer = 'Bearer ' . get_setting(request()->user_id, $type);
    $headers['Authorization'] = $bearer;
    $headers['Content-Type'] = "application/json";
    $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);

    $options = [];
    if (!empty($data)) {
        $options['body'] = $data;
    }

    $response = $client->request('GET', $url, $options);
    $bd = $response->getBody()->getContents();
    $bd = json_decode($bd);

    return $bd;
}

// Renew token cron job
function tokens_renew()
{
       @ini_set('max_execution_time', 0);
         @set_time_limit(0);

    $token='';
    $uid='';
    $res=Setting::where('Key', 'planning_refresh_token')->get();
    foreach($res as $data)
    {
        $token=$data->value;
        $uid=$data->location_id;

         $response = Http::post('https://api.planningcenteronline.com/oauth/token', [
        'grant_type' => 'refresh_token',
        'refresh_token' => $token,
        'client_id' => getAccessToken($type='planning_client_id'),
        'client_secret' => getAccessToken($type='planning_client_sceret'),
    ]);
    if ($response->successful()) {
        $data = $response->json();
        $new_access_token = $data['access_token'];
        $new_refresh_token = $data['refresh_token'];

        save_setting('planning_access_token',$new_access_token,$uid);
        save_setting('planning_refresh_token',$new_refresh_token,$uid);


    }
      else
      {
        echo 'not recevied';
      }

    }



}
