<?php

namespace App\Services;

use App\Models\ChurchEvent;
use Illuminate\Support\Facades\Http;

class ChurchEventService
{
    protected $apiUser = 'radiwa6602@dwakm.com';
    protected $apiKey  = '2b98fda4b8c22b26d7da69d816bf3ae7';


    public function fetchEvents()
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
            ])->get('https://churchmetrics.com/api/v1/events.json');

            if (!$response->successful()) {
                \Log::warning('Events API Failed', ['status' => $response->status()]);
                return [];
            }

            $apiEvents = $response->json();

            return collect($apiEvents)->map(function ($event) {
                return (object) [
                    'id' => $event['id'],
                    'name' => $event['name'],
                    'created_at' => now(),
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Events API Error: ' . $e->getMessage());
            return [];
        }
    }


    public function createEventToAPI($name)
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
            ])->post('https://churchmetrics.com/api/v1/events.json', [
                'name' => $name
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data ?? null;  //$data['id'] 
            }

            \Log::error('Event Create API Failed', ['status' => $response->status(), 'response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            \Log::error('Event Create API Error: ' . $e->getMessage());
            return null;
        }
    }


    public function updateEventOnAPI($cm_id, $name)
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
            ])->put("https://churchmetrics.com/api/v1/events/{$cm_id}.json", [
                'name' => $name
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Event Update API Error: ' . $e->getMessage());
            return false;
        }
    }




    public function deleteEventOnAPI($cm_id)
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
            ])->delete("https://churchmetrics.com/api/v1/events/{$cm_id}.json");

            if (!$response->successful()) {
                \Log::error('Event Delete API Failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Event Delete API Error: ' . $e->getMessage());
            return false;
        }
    }
}
