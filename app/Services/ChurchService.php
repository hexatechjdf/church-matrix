<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CrmToken;

class ChurchService
{
    public function saveApiCredentials(array $data)
    {
        $user = loginUser();
        $ty = $user->role == 0 ? 'admin' : 'location';
        return CrmToken::updateOrCreate(['user_id' => $user->id,
            'crm_type' => 'church'],[
            'location_id' => $user->location,

            'user_type' => $ty,
            'access_token' => $data['church_matrix_user'],
            'refresh_token'  => $data['church_matrix_api'],
        ]);
    }

    public function fetchRegions()
    {
        return $this->request('GET', 'regions.json');
    }

    public function saveChurchSetting(int $regionId)
    {
        $user = loginUser();
        CrmToken::where(['user_id' => $user->id,'crm_type' => 'church'])->update([
            'company_id' => $regionId,
        ]);
    }

    public function saveLocation($locationId)
    {
        $user = loginUser();
        CrmToken::where(['user_id' => $user->id,'crm_type' => 'church'])->update([
            'location_id' => $locationId,
        ]);
    }

   public function request($method, $url, $data = [],$header_required = false)
    {
        $baseurl = 'https://churchmetrics.com/api/v1/';
        $endpoint = $baseurl . $url;

        $auth_key = '2b98fda4b8c22b26d7da69d816bf3ae7';
        $auth_user = 'radiwa6602@dwakm.com';
        try {
            $client = Http::withHeaders([
                'X-Auth-User' => $auth_user,
                'X-Auth-Key'  => $auth_key,
            ]);

            if (strtoupper($method) === 'GET') {
                $response = $client->get($endpoint, $data);
            }
            else if (strtoupper($method) === 'POST') {
                $response = $client->post($endpoint, $data);
            }
            else if (strtoupper($method) === 'PUT') {
                $response = $client->put($endpoint, $data);
            }
            else if (strtoupper($method) === 'PATCH') {
                $response = $client->patch($endpoint, $data);
            }
            else if (strtoupper($method) === 'DELETE') {
                $response = $client->delete($endpoint);
            }
            else {
                throw new \Exception("Unsupported HTTP method: $method");
            }
            $r =  $response->successful() ? $response->json() : false;
            return $header_required ? [$r,@$response->getHeader('Link')] : $r;

        } catch (\Exception $e) {
            \Log::error("ChurchMetrics API error: ".$e->getMessage());
            return false;
        }
    }
}
