<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CrmToken;
use Illuminate\Support\Facades\Cache;

class ChurchService
{
    public function saveApiCredentials(array $data)
    {
        $user = loginUser();
        $ty = $user->role == 0 ? 'admin' : 'location';
        if ($ty == 'location') {
            $user->church_admin = false;
            $user->save();
        }
        return CrmToken::updateOrCreate([
            'user_id' => $user->id,
            'crm_type' => 'church'
        ], [
            'location_id' => $user->location,
            'user_type' => $ty,
            'access_token' => $data['church_matrix_user'],
            'refresh_token'  => $data['church_matrix_api'],
        ]);
    }

    public function fetchEvents()
    {
        $cacheKey = 'church_events';
        return Cache::remember($cacheKey, 60 * 60, function () {
            $url = "events.json";
            list($data, $apiEvents) = $this->request('GET', $url, [], true);

            return $data;
        });
    }


    public function fetchCategories()
    {
        
        $cacheKey = 'church_categories';
        return Cache::remember($cacheKey, 60 * 60, function () {
            $url = "categories.json";
            list($data, $apiEvents) = $this->request('GET', $url, [], true);

            return $data;
        });
    }
    public function fetchCampuses($id = null)
    {
        $user = loginUser($id);
        $campuses = [];
        if ($user->church_admin) {
            $t = getChurchToken('location', $user->id);
            $cacheKey = "campuses_{$user->id}";
            $campuses = Cache::remember($cacheKey, 600, function () use ($t) {
                $url = "campuses.json";
                list($data, $linkHeader) = $this->request('GET', $url, [], true, $t);
                return collect($data)->map(function ($event) {
                    return [
                        'id'         => $event['id'],
                        'name'       => $event['slug'],
                        'created_at' => now(),
                    ];
                })->toArray();
            });
        } else {
            $c = @$user->campus;
            $campuses['id'] = @$c->campus_unique_id;
            $campuses['name'] = @$c->name;
        }

        return $campuses;
    }

    public function fetchRegions($crm = null)
    {
        return $this->request('GET', 'regions.json', [], false, $crm);
    }

    public function saveChurchSetting($id, $key)
    {
        $user = loginUser();
        $t = $user->churchToken;
        if (!$t) {
            return "Church token not found";
        }

        $t->$key = $id;
        $t->save();
    }


    public function request($method, $url, $data = [], $header_required = false, $crm = null)
    {
        $baseurl = 'https://churchmetrics.com/api/v1/';
        $endpoint = $baseurl . $url;

        $crm = $crm ?? getChurchToken();


        $auth_key = $crm->refresh_token ?? '2b98fda4b8c22b26d7da69d816bf3ae7';
        $auth_user = $crm->access_token ?? 'radiwa6602@dwakm.com';
        try {
            $client = Http::withHeaders([
                'X-Auth-User' => $auth_user,
                'X-Auth-Key'  => $auth_key,
            ]);

            if (strtoupper($method) === 'GET') {
                $response = $client->get($endpoint, $data);
            } else if (strtoupper($method) === 'POST') {
                $response = $client->post($endpoint, $data);
            } else if (strtoupper($method) === 'PUT') {
                $response = $client->put($endpoint, $data);
            } else if (strtoupper($method) === 'PATCH') {
                $response = $client->patch($endpoint, $data);
            } else if (strtoupper($method) === 'DELETE') {
                $response = $client->delete($endpoint);
            } else {
                throw new \Exception("Unsupported HTTP method: $method");
            }

            $r =  $response->successful() ? $response->json() : false;
            return $header_required ? [$r, @$response->getHeader('Link')] : $r;
        } catch (\Exception $e) {
            \Log::error("ChurchMetrics API error: " . $e->getMessage());
            return false;
        }
    }
}
