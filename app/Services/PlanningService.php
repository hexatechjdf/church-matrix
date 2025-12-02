<?php

namespace App\Services;

class PlanningService
{
    public $base = 'https://api.planningcenteronline.com/';

    function planning_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $a_token = null)
    {
        $type = 'planning_access_token';
        $baseurl = $this->base;

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


        $response = $client->request($method, $url1, $options);
        $bd = $response->getBody()->getContents();
        $isaccessdenied=false;
        if(strpos($bd,'HTTP Basic: Access denied')!==false){
            $isaccessdenied=true;
        }

        $bd = json_decode($bd);

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

    public function saveToken($user_id)
    {

    }

    public function getToken()
    {

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
        }



    }
}
