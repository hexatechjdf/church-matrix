<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CrmToken;
use Illuminate\Support\Facades\Cache;
use App\Jobs\churchmatrix\ServiceTimeJob;

class ChurchService
{
    public function saveApiCredentials(array $data)
    {
        $user = loginUser();
        $ty = $user->role == 0 ? 'admin' : 'location';

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
        $cacheKey = 'church_eventsss';
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

            $d = $this->manageCategoryGroups($data);

            return $d;
        });
    }

    public function manageCategoryGroups($records)
    {
        $grouped = [];

        foreach ($records as $item) {
            if ($item['parent_id'] === null) {
                $grouped[$item['id']] = [
                    'parent' => $item,
                    'children' => []
                ];
            }
        }

        foreach ($records as $item) {
            if ($item['parent_id'] !== null) {
                $grouped[$item['parent_id']]['children'][] = $item;
            }
        }

        return $grouped;
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
        }

        return $campuses;
    }

    public function fetchRegions($crm = null)
    {
        return Cache::remember('regions', 600, function () use ($crm) {
            return $this->request('GET', 'regions.json', [], false, $crm);
        });

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


    public function request($method, $url, $data = [], $header_required = false, $crm = null,$show_complete = false)
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

            if($show_complete)
            {
               return $response->json();
            }

            $r =  $response->successful() ? $response->json() : false;
            return $header_required ? [$r, @$response->getHeader('Link')] : $r;
        } catch (\Exception $e) {
            \Log::error("ChurchMetrics API error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserCampusId($request,$user)
    {
        $campus_id = null;
        try {
            $campus_id = $user->church_admin
                ? $request->campus_id
                : @getCampusSession()['campus_id'];
        } catch (\Exception $e) {}

        return $campus_id;
    }

    public function getCacheTimes($user)
    {
        $cacheKey = "service_time_temp_{$user->id}";
        $all = cache()->get($cacheKey);
        if (empty($all)) {
            dispatch_sync(new ServiceTimeJob($user->id, false));
            $all = cache()->get($cacheKey);
        }

        return $all;
    }

    public function setRecordData($user_id,$record)
    {
        list($year, $week) = $this->decodeWeekReference($record['week_reference']);

        return [
            'user_id'          => $user_id,
            'record_unique_id'          => @$record['id'],
            'organization_unique_id'    => @$record['organization_id'],
            'week_reference'            => @$record['week_reference'],
            'week_no'                   => @$week,
            'week_volume'               => @$year . '_' . @$week,
            'service_date_time'         => @$record['service_date_time'],
            'service_timezone'          => @$record['service_timezone'],
            'value'                     => @$record['value'],
            'service_unique_time_id'    => @$record['service_time_id'],
            'event_unique_id'           => @$record['event']['id'] ?? null,
            'event_name'           => @$record['event']['name'] ?? null,
            'category_unique_id'        => @$record['category']['id'] ?? null,
            'category_name'        => @$record['category']['name'] ?? null,
            'campus_unique_id'          => @$record['campus']['id'],
            'campus_name'          => @$record['campus']['slug'],
            'record_created_at'         => @$record['created_at'],
            'record_updated_at'         => @$record['updated_at'],
            'created_at'                => now(),
            'updated_at'                => now(),
        ];
    }

    public function decodeWeekReference($week_reference)
    {
        $baseYear = 1970;
        $year = $baseYear + intdiv($week_reference, 52);
        $week = $week_reference % 52;
        if ($week === 0) $week = 52;

        return [$year, $week];
    }

}
