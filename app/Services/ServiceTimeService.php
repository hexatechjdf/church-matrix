<?php

namespace App\Services;

use App\Models\ServiceTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServiceTimeService
{
    protected $apiUser = 'radiwa6602@dwakm.com';
    protected $apiKey  = '2b98fda4b8c22b26d7da69d816bf3ae7';

    /**
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetchAll()
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
                'Accept'      => 'application/json',
            ])->get('https://churchmetrics.com/api/v1/service_times');

            if (!$response->successful()) {
                Log::warning('ServiceTimes API Failed', ['status' => $response->status(), 'body' => $response->body()]);
                return collect();
            }

            $apiTimes = $response->json();

            if (empty($apiTimes)) {
                Log::info('ServiceTimes API returned empty data.');
                return collect();
            }

            foreach ($apiTimes as $time) {
                ServiceTime::updateOrCreate(
                    ['cm_id' => $time['id']],
                    [
                        'campus_id'          => $time['campus']['id'] ?? null,
                        'day_of_week'        => $time['day_of_week'] ?? null,
                        'time_of_day'        => isset($time['time_of_day']) ? date('H:i:s', strtotime($time['time_of_day'])) : null,
                        'timezone'           => $time['timezone'] ?? $time['campus']['timezone'] ?? null,
                        'relation_to_sunday' => $time['relation_to_sunday'] ?? null,
                        'date_start'         => $time['date_start'] ?? null,
                        'date_end'           => $time['date_end'] ?? null,
                        'replaces'           => $time['replaces'] ?? null,
                        'event_id'           => $time['event']['id'] ?? null,
                    ]
                );
            }

            return ServiceTime::with('event')->get();
        } catch (\Exception $e) {
            Log::error('ServiceTimes API Error: ' . $e->getMessage());
            return collect();
        }
    }

    public function createServiceTimeToAPI(array $data)
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
            ])->post('https://churchmetrics.com/api/v1/service_times.json', $data);

            if ($response->successful()) {
                $result = $response->json();
                return $result['id'] ?? null;
            }

            Log::error('ServiceTime API Create Failed', ['status' => $response->status(), 'response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('ServiceTime API Create Error: ' . $e->getMessage());
            return null;
        }
    }

    public function updateServiceTimeOnAPI($cm_id, array $data)
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $this->apiUser,
                'X-Auth-Key'  => $this->apiKey,
            ])->put("https://churchmetrics.com/api/v1/service_times/{$cm_id}.json", $data);

            if (!$response->successful()) {
                Log::error('ServiceTime API Update Failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('ServiceTime API Update Error: ' . $e->getMessage());
            return false;
        }
    }
}
