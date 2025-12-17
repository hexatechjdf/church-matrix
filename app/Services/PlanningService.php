<?php

namespace App\Services;

use App\Models\CrmToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;


class PlanningService
{
    public $base = 'https://api.planningcenteronline.com/';

    function planning_api_call($url = '', $method = 'get', $data = '', $headers = [], $json = false, $a_token = null, $crm = null)
    {
        $type = 'planning_access_token';
        $baseurl = $this->base;

        $user_id = request()->user_id ?? 883;
        $user = loginUser($user_id);

        $crm = $crm ?? $user->planningToken;

        if (is_null($a_token)) {
            $bearer = $crm->access_token;
        } else {
            $bearer = $a_token;
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

            // Check for access denied
            if (strpos($bd, 'HTTP Basic: Access denied') !== false) {
                return (object) ['data' => [], 'included' => [], 'meta' => []];
            }

            $bd = json_decode($bd);

            // Handle unauthorized errors
            if ($bd && property_exists($bd, 'errors') && is_array($bd->errors) && count($bd->errors) > 0) {
                foreach ($bd->errors as $error) {
                    if (property_exists($error, 'code') && strtolower($error->code) == 'unauthorized') {
                        $refresh_token = $crm->refresh_token;

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

            // Ensure we always return an object
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

    // Renew token cron job
    public function tokens_renew()
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $token = '';
        $uid = '';
        $res = CrmToken::where('crm_type', 'planning')->get();
        // $res=Setting::where('Key', 'planning_refresh_token')->get();
        foreach ($res as $data) {
            $token = $data->refresh_token;
            $uid = $data->user_id;
            $response = Http::post('https://api.planningcenteronline.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token,
                'client_id' => getAccessToken($type = 'planning_client_id'),
                'client_secret' => getAccessToken($type = 'planning_client_sceret'),
            ]);
            if ($response->successful()) {
                $data = $response->json();
                $payload = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                ];
                $this->saveToken($uid, $payload);
            }
        }
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
