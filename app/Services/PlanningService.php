<?php

namespace App\Services;

use App\Models\CrmToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;


class PlanningService
{
    public $base = 'https://api.planningcenteronline.com/';

    protected int $user_id;
    protected string $token;

    /**
     * Constructor to set user_id and token
     */
    public function setUserToken(int $user_id, string $token)
    {
        $this->user_id = $user_id;
        $this->token = $token;
    }

    function planning_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $a_token = null, $crm = null)
    {

        // $this->tokens_renew();
        $type = 'planning_access_token';
        $baseurl = $this->base;


        // make it by function parameter
        $user_id = $this->user_id ?? request()->user_id;
        // ..........

        $user = loginUser($user_id);


        $crm = $this->token ?? $crm ?? $user->planningToken;

        if (is_null($a_token) && !@$this->token) {
            $bearer = $crm->access_token;
        } else {
            $bearer = $this->token ?? $a_token;
        }

        if (empty($bearer)) {
            // Return empty object instead of empty string
            return (object) ['data' => [], 'included' => [], 'meta' => []];
        }

        $location = @$user->crmtoken->location_id;
        request()->location_id = $location;
        $headers['Authorization'] = 'Bearer ' . $bearer;
        $headers['Content-Type'] = "application/json";

        try {
            $client = new \GuzzleHttp\Client([
                'http_errors' => false,
                'headers' => $headers,
                'timeout' => 30, // Add timeout
                'connect_timeout' => 10,
            ]);

            $options = [];
            if (!empty($data) && $method != 'get') {
                $options['body'] = $data;
            }

            $url1 = $baseurl . $url;



            $response = $client->request($method, $url1, $options);
            $bd = $response->getBody()->getContents();
            //   \Log::info([$bd]);

            // Check for access denied
            if (strpos($bd, 'HTTP Basic: Access denied') !== false) {
                return (object) ['data' => [], 'included' => [], 'meta' => []];
            }

            $bd = json_decode($bd);

            // Handle unauthorized errors
            if ($bd && property_exists($bd, 'errors') && is_array($bd->errors) && count($bd->errors) > 0) {
                foreach ($bd->errors as $error) {
                    if (property_exists($error, 'code') && strtolower($error->code) == 'unauthorized') {
                        $refresh_token = @$crm->refresh_token ?? '';
                        if(empty($refresh_token))
                        {
                            break;
                        }
                        $lck = Cache::lock('planning_cache_lock_' . $user_id, 40);
                        $is_refresh = false;
                        $new_token = null;

                        try {
                            list($is_refresh, $new_token) = $lck->block(40, function () use ($user_id, $refresh_token, $crm) {
                                $code = $this->get_planning_token($refresh_token, 'refresh_token');

                                if ($code && property_exists($code, 'access_token')) {
                                    $payload = [
                                        'access_token' => $code->access_token,
                                        'refresh_token' => $code->refresh_token,
                                    ];
                                    $this->saveToken($user_id, $payload);
                                    return [true, $code->access_token];
                                }

                                if (
                                    $code && property_exists($code, 'error_description') &&
                                    $code->error_description == 'The refresh token is no longer valid'
                                ) {
                                    $payload = [
                                        'access_token' => null,
                                        'refresh_token' => null,
                                    ];
                                    $this->saveToken($user_id, $payload);
                                }
                                return [false, null];
                            });
                        } catch (\Exception $e) {
                            \Log::error('Token refresh failed: ' . $e->getMessage());
                        }

                        if ($is_refresh && $new_token) {
                            return $this->planning_api_call($url, $method, $data, $headers, $json, $new_token);
                        }

                        return (object) ['data' => [], 'included' => [], 'meta' => []];
                    }
                }
            }

            return $bd ?: (object) ['data' => [], 'included' => [], 'meta' => []];
        } catch (\Exception $e) {
            \Log::error('Planning API call failed: ' . $e->getMessage());
            return (object) ['data' => [], 'included' => [], 'meta' => []];
        }
    }

    public function saveToken($user_id, $data)
    {
        CrmToken::updateOrCreate(['user_id' => $user_id, 'crm_type' => 'planning'], $data);
    }

    public function get_planning_token($code, $type = "")
    {
        $url = $this->base . 'oauth/token';
        $headers['Content-Type'] = "application/json";
        $client = new \GuzzleHttp\Client(['http_errors' => false, 'headers' => $headers]);
        $options = [];
        $codekey = empty($type) ? "code" : "refresh_token";
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
        $resp->payload = $data;
        $resp->method = 'post';
        $resp->responseback = $bd;

        return $bd;
    }

  public function fetchPlanningToken($code, $user_id, $type = 'code',$save=true)
  {
            $payload = [
                'grant_type' => $type == 'code' ? 'authorization_code' : 'refresh_token',
                $type => $code,
                'client_id' => getAccessToken( 'planning_client_id'),
                'client_secret' => getAccessToken( 'planning_client_sceret'),
            ];

            if($type== 'code')
            {
                $payload['redirect_uri'] = route('planningcenter.callback') . "?location_id=" . $user_id;
            }
            $token = [];
            $status=false;
            $response = Http::post($this->base .'oauth/token', $payload);
            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? $data;
                if($token)
                {

                    $status=true;
                    $payload = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'user_id' => $user_id,
                    'crm_type' => 'planning'
                    ];
                    $token = $payload;
                    if($save){
                            $this->saveToken($user_id, $payload);
                    }

                }


            }

            return [$status,(object)$token];
  }



    public function getHeadcounts($offset, $token = null, $filter = null)
    {
        $url = "check-ins/v2/headcounts?include=attendance_type,event_time,event&per_page=1000&offset=" . $offset;

        $query = [];
        if ($filter) {

            foreach ($filter as $key => $value) {
                $query['where' . $key] = $value;
            }
        }


        if (!empty($query)) {
            $url .= "&" . http_build_query($query);
        }
        //  dd($url);

        return $this->planning_api_call($url, 'get', '', [], false, $token);
    }

    public function getEvents($offset, $token = null)
    {
        $url = "check-ins/v2/events?include=attendance_types&per_page=1000&offset=" . $offset;
        return $this->planning_api_call($url, 'get', '', [], false, $token);
    }

    public function buildIncludedMap(array $included): Collection
    {

        $included  = collect($included)->keyBy(function ($item) {
            return $item->type . '.' . $item->id;  // "Event.123"
        });
        return $included;
    }
}
