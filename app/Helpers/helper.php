<?php

use App\Models\Contact;
use App\Models\Locations;
use App\Models\Setting;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Client\Request;
use PhpParser\Node\Stmt\TryCatch;

use Illuminate\Support\Facades\Cache;



function login_id($id = "")
{
    if (!empty($id)) {
        return $id;
    }
    // if (session('location_id')) {
    //     return session('location_id');
    // }
    // if (request()->has('location_id')) {
    //     return request()->has('location_id');
    // }
    $id = auth()->user()->id;

    if (auth()->user()->role == 1) {
        return $id;
    }
    return $id;
}

// Set Custom Fields
if (!function_exists('setCustomFields')) {
    function setCustomFields($request)
    {


        // ['status'=>'12','mydata'=>['value'=>'2','type'=>'MULTIPLE_OPTIONS','options'=>['1','2','3']]];
        $user_custom_fields = new \stdClass;
        try {
            $request_array = [];
            foreach ($request as $key => $value) {
                $key  = str_replace([' ', '_'], '', $key);
                $key = strtolower($key);
                $request_array[$key] = $value;
            }


            $ghl_custom_values = ghl_api_call('custom-fields');

            // dd($ghl_custom_values);
            $custom_values = $ghl_custom_values;

            if (property_exists($custom_values, 'customFields')) {
                $custom_values = $custom_values->customFields;

                $custom_values = array_filter($custom_values, function ($value) use ($request_array) {
                    $kn = strtolower(str_replace([' ', '_'], '', $value->name));
                    // $kn = strtolower(str_replace(' ', '_', $value->name));
                    return in_array($kn, array_keys($request_array));
                });
                // dd($custom_values);
                foreach ($custom_values as $key => $custom) {
                    $key  = str_replace([' ', '_'], '', $custom->name);
                    $key = strtolower($key);
                    $custom->value = $request_array[$key];
                    $request_array[$key] = $custom;
                }
                //   dd($custom_values);
                $i = 0;
                $v = 0;
                $all_keys = array_keys($request);

                foreach ($request_array as $key => $custom) {
                    $i++;
                    $value = '';
                    $title = $all_keys[$v];
                    $v++;
                    $id = null;
                    $lttitle = strtolower($title);
                    $type = strpos($lttitle, 'date') !== false ? 'TEXT' : 'TEXT';
                    if ($type == 'TEXT') {
                        $type = strpos($lttitle, 'amount') !== false || strpos($key, 'total_paid') !== false || strpos($lttitle, 'grand_total') !== false ? 'MONETORY' : 'TEXT';
                    }
                    $name = str_replace('_', ' ', $title);
                    $name = ucwords($name);
                    $vdata = ['name' => $name];

                    if (is_object($custom)) {
                        $id = $custom->id;
                        $value = $custom->value;
                        if (is_object($value)) {
                            $options = $value->options ?? [];
                            $type = $value->type ?? $type;
                            $value = $value->value ?? '';
                        }
                        $options = [];
                        $always = false;
                        $ignore = false;
                        $isreturn = false;
                        if (is_array($value)) {
                            $always = isset($value['options']);
                            $isreturn = isset($value['isreturn']);
                            $ignore = isset($value['ignore']); // in order to stop updating
                            $options = $value['options'] ?? [];
                            $type = $value['type'] ?? $type;
                            $value = $value['value'] ?? '';

                            if ($isreturn) {
                                $value = $custom;
                                $user_custom_fields->$id = $value;
                            }
                        }
                        if ($ignore) {
                            continue;
                        }
                        $vdata['dataType'] = $type;
                        $vdata['options'] = $options;

                        $vdata = json_encode($vdata);
                        if ($custom->name != $name || $custom->dataType != $type || $always) {
                            if ($i % 15 == 0) {
                                sleep(2);
                            }
                            $abc =  ghl_api_call('custom-fields/' . $custom->id, 'PUT', $vdata, [], true);
                        }
                    } else {



                        $value = $custom;


                        if (is_object($value)) {
                            $options = $value->options ?? [];
                            $type = $value->type ?? $type;
                            $value = $value->value ?? '';
                        }
                        if (is_array($value)) {
                            $options = $value['options'] ?? [];
                            $type = $value['type'] ?? $type;
                            $value = $value['value'] ?? '';
                        }




                        $vdata['dataType'] = $type ?? 'TEXT';
                        $vdata['pickListOptions'] = $options ?? '';

                        $vdata = json_encode($vdata);
                        if ($i % 15 == 0) {
                            sleep(2);
                        }

                        // dd($vdata);
                        // ghl_api_call('custom-fields/' . $custom->id, 'PUT', $vdata,[], true);
                        $cord = ghl_api_call('custom-fields', 'POST', $vdata, [], true);
                        // dd($abc);
                        if ($cord && property_exists($cord, 'id')) {
                            $id = $cord->id;
                        }
                    }

                    if ($id) {
                        $user_custom_fields->$id = $value;
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $user_custom_fields;
    }
}


// sendTagOrCustomFieldsToGhl

if (!function_exists('sendTagOrCustomFieldsToGhl')) {
    function sendTagOrCustomFieldsToGhl($contact_id, $tag = '', $customFields = null)
    {

        if (!is_object($contact_id)) {
            $contact_id = str_replace(' ', '', $contact_id);
            $response = ghl_api_call('contacts/' . $contact_id);
        } else {
            $response = new \stdClass;
            $response->contact = $contact_id;
        }
        if ($response && property_exists($response, 'contact')) {
            $contact = $response->contact;


            if (!empty($tag)) {
                if (!is_array($contact->tags)) {
                    $contact->tags = [];
                }
                if (is_array($tag)) {
                    $contact->tags = array_merge($contact->tags, $tag);
                } else {
                    $contact->tags[] = $tag;
                }
            }


            if ($customFields) {
                $contact->customField = $customFields;
            }

            $response = ghl_api_call('contacts/' . $contact_id, 'PUT', json_encode($contact), [], true);
        }
    }
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


// GHL Oauth CAll
if (!function_exists('ghl_oauth_call')) {

    function ghl_oauth_call($code = '', $method = '')
    {
        $url = 'https://services.leadconnectorhq.com/oauth/token';
        $curl = curl_init();
        $data = [];
        $data['client_id'] = getAccessToken('crm_client_id');
        $data['client_secret'] = getAccessToken('crm_client_secret');
        $md = empty($method) ? 'code' : 'refresh_token';
        $data[$md] = $code; // (empty($code)?company_user()->ghl_api_key:$code);
        $data['grant_type'] = empty($method) ? 'authorization_code' : 'refresh_token';
        //   $data['grant_type'] =  'authorization_code';
        $postv = '';
        $x = 0;

        foreach ($data as $key => $value) {
            if ($x > 0) {
                $postv .= '&';
            }
            $postv .= $key . '=' . $value;
            $x++;
        }

        $curlfields = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postv,
        );
        //dd($url,$postv);
        curl_setopt_array($curl, $curlfields);

        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        return $response;
    }
}

// GHL GET  Token
function ghl_token($code, $type = '', $method = 'view')
{

    $code  =  ghl_oauth_call($code, $type);

    if ($code) {
        if (property_exists($code, 'access_token')) {
            $u = User::where('location', $code->locationId)->first();
            if (!$u) {
                if($method=='view'){
                     abort(redirect()->route('auth.check'));
                }
                else{
                    return null;
                }
            }
            $ui = null;
            if ($u) {
                $ui = $u->id;
            }
            session()->put('ghl_api_token', $code->access_token);
            session()->put('ghl_location_id', $code->locationId);

            save_setting('ghl_access_token', $code->access_token, $ui);
            save_setting('ghl_refresh_token', $code->refresh_token, $ui);
            save_setting('ghl_location_id', $code->locationId??"", $ui);
            save_setting('ghl_company_id', $code->companyId??"", $ui);
            save_setting('ghl_user_type', $code->userType??"", $ui);
            if ($method == 'view') {
                abort(redirect()->route('auth.check')->with('success', "connected"));
            } else {
                return true;
            }
        } else {
            if (property_exists($code, 'error_description')) {
                if (empty($type)) {
                    if ($method == 'view') {
                        abort(redirect()->route('auth.check')->with('error', $code->error_description));
                    }
                    //return false;
                }
            }
            //return null;
        }
    }
    // if (empty($type)) {
    //     abort(redirect()->route('dashboard')->with('error', 'Server error'));
    // }
    return null;
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

// updated ghl api call funciton per the auth 2.0


function ConnectOauth($loc,$token,$method=''){

    $tokenx=false;

     $callbackurl = route('crm.callback');
                $locurl = "https://services.leadconnectorhq.com/oauth/authorize?location_id=" . $loc . "&response_type=code&userType=Location&redirect_uri=" . $callbackurl . "&client_id=" . getAccessToken('crm_client_id') . "&scope=contacts.write contacts.readonly locations.readonly locations/customFields.readonly locations/customValues.write users.readonly users.write locations/customFields.write";

                    $client = new \GuzzleHttp\Client(['http_errors' => false]);
                    $headers = [
                        'Authorization' => 'Bearer ' . $token
                    ];
                    $request = new \GuzzleHttp\Psr7\Request('POST',   $locurl, $headers);
                    $res1 = $client->sendAsync($request)->wait();
                    $red =  $res1->getBody()->getContents();
                    $red = json_decode($red);

                    if ($red && property_exists($red, 'redirectUrl')) {
                        $url = $red->redirectUrl;
                        $parts = parse_url($url);
                        parse_str($parts['query'], $query);
                        $code = $query['code'];

                        $tokenx  = ghl_token($code, '', 'eee3');
                    }

    return $tokenx;

}

// Modified by me according to this project
function ghl_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $is_v2 = true)
{

    $baseurl = 'https://rest.gohighlevel.com/v1/';
    $bearer = 'Bearer ';
    // if (get_default_settings('oauth_ghl', 'api') != 'oauth') {
    //     $token = company_user()->ghl_api_key;

    // } else {
    $user_id=request()->user_id;
    $token = get_setting($user_id, 'ghl_access_token');
    // dd($token);
    if (empty($token)) {
        if (session('cronjob')) {
            return false;
        }
        return 'token not found';
    }
    $baseurl = 'https://services.leadconnectorhq.com/';
    $version = get_default_settings('oauth_ghl_version', '2021-04-15');
    $location = get_setting($user_id, 'ghl_location_id');
    $headers['Version'] = $version;
    if ($method == 'get' || $method == 'GET') {
        $url .= (strpos($url, '?') !== false) ? '&' : '?';
        if (strpos($url, 'location_id=') === false && strpos($url, 'locationId=') === false) {
            $url .= 'locationId=' . $location;
        }
    }
    if (strpos($url, 'custom') !== false && strpos($url, 'locations/') !== false) {
        $url = 'locations/' . $location . '/' . $url;
    }

    if ($token) {
        $headers['Authorization'] =  $bearer . $token;
    }
    $headers['Content-Type'] = "application/json";
    $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);
    // dd($client);
    $options = [];
    if (!empty($data)) {
        $options['body'] = $data;
        // saveLogger('ghl_data', json_encode($data));
        // dd($data);
    }

    $url1 = $baseurl . $url;

    $response = $client->request($method, $url1, $options);

    $bd = $response->getBody()->getContents();

    $bd = json_decode($bd);

    if (isset($bd->error) && strtolower($bd->error) == 'unauthorized') {

        $code  = get_setting($user_id, 'ghl_refresh_token');




        if (strpos(strtolower($bd->message),'authclass')===false) {
            $lck=Cache::lock('crm_cache_lock_'.$user_id,40);
        $is_refresh=false;
            try{
            list($is_refresh,$a_token)= $lck->block(40, function () use ($user_id,$code) {
    $newrefresh_token = get_setting($user_id, 'ghl_refresh_token');
    if($newrefresh_token==''){
        return false;
    }

    if($code!=$newrefresh_token){
        return true;
    }
    return $tok = ghl_token($code, '1','2');

    });

    if($is_refresh){
               sleep(1);
                return ghl_api_call($url, $method, $data, $headers, $json, $is_v2);
            }
        }catch(\Exception $e){

        }

        }













        if (session('cronjob')) {
            return false;
        }

    }
    return $bd;
}

function get_default_settings($j, $k)
{

    return $k;
}


// GHL TO Planning Center
function crm_Lead($lead)
{


}


// Planning Cente To GHL
function planning_center_Lead($lead)
{
    // \DB::table('logs')->insert(['message'=>'lead-captured']);


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
