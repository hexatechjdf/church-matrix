<?php

namespace App\Services;

use App\Models\CrmToken;
use Illuminate\Support\Facades\Cache;

class PlanningService
{
    public $base = 'https://api.planningcenteronline.com/';

    function planning_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $a_token = null,$crm = null)
    {
        $type = 'planning_access_token';
        $baseurl = $this->base;

        $user_id  = request()->user_id ?? 886;
        $user = loginUser($user_id);

        $crm = $crm ?? $user->planningToken;


        if (is_null($a_token)) {
            $bearer = $crm->access_token;
        } else {
            $bearer =  $a_token;
        }

        if(empty($bearer)){
        return '';
        }

        $location = @$user->crmtoken->location_id;
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
            $refresh_token = $crm->refresh_token;
            // dd($bd);

            $lck=Cache::lock('planning_cache_lock_'.$user_id,40);
            $is_refresh=false;
            try{
                list($is_refresh,$a_token)= $lck->block(40, function () use ($user_id,$refresh_token,$crm) {
                    // $newrefresh_token = $crm->refresh_token;
                    // if($newrefresh_token==''){
                    //     return [false,''];
                    // }
                    // if($refresh_token!=$refresh_token){
                    //     return [true,''];
                    // }

                    $payload = [];
                    $code = $this->get_planning_token($refresh_token, 'refresh_token');
                    if($code && property_exists($code,'access_token')){
                        $payload = [
                          'access_token' => $code->access_token,
                          'refresh_token' => $code->refresh_token,
                        ];
                        $this->saveToken($user_id,$payload);
                        return [true,$code->access_token];
                    }
                    if($code && property_exists($code,'error_description') && $code->error_description=='The refresh token is no longer valid'){
                        $payload = [
                          'access_token' => $user_id,
                          'refresh_token' => $user_id,
                          'company_id' => $user_id,
                          'organization_name' => $user_id,
                        ];
                        $this->saveToken($user_id,$payload);
                    }
                    return [false,''];
                });
            }catch(\Exception $e){

            }

            if($is_refresh){
                return $this->planning_api_call($url , $method, $data , $headers , $json,$a_token);
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

    public function saveToken($user_id,$data)
    {
       CrmToken::updateOrCreate(['user_id' => $user_id, 'crm_type' => 'planning'],$data);
    }

    public function get_planning_token($code, $type = "")
    {
        $url = $this->base.'oauth/token';
        $headers['Content-Type'] = "application/json";
        $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);
        $options = [];
        $codekey=empty($type) ? "code" : "refresh_token";
        $data = [
            "grant_type" => empty($type) ? "authorization_code" : "refresh_token",
            $codekey => $code,
            "client_id" => getAccessToken('planning_client_id'),
            // "client_secret" => getAccessToken('planning_client_sceret'),
            "client_secret" => getAccessToken('planning_client_secret'),
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

        return $bd;
    }

    // Renew token cron job
    public function tokens_renew()
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $token='';
        $uid='';
        $res=CrmToken::where('crm_type','planning')->get();
        // $res=Setting::where('Key', 'planning_refresh_token')->get();
        foreach($res as $data)
        {
            $token=$data->refresh_token;
            $uid=$data->user_id;
            $response = Http::post('https://api.planningcenteronline.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token,
                'client_id' => getAccessToken($type='planning_client_id'),
                'client_secret' => getAccessToken($type='planning_client_sceret'),
            ]);
            if ($response->successful()) {
                $data = $response->json();
                $payload = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                ];
                $this->saveToken($uid,$payload);
            }
        }



    }
}
