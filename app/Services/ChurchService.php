<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChurchService
{
   public function request($method, $url, $data = [],$header_required = false)
    {
        $baseurl = 'https://churchmetrics.com/api/v1/';
        $endpoint = $baseurl . $url;

        try {
            $client = Http::withHeaders([
                'X-Auth-User' => 'radiwa6602@dwakm.com',
                'X-Auth-Key'  => '2b98fda4b8c22b26d7da69d816bf3ae7',
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
