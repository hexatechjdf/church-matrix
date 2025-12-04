<?php

namespace App\Services;

use App\Models\ServiceTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServiceTimeService
{
    protected $apiUser = 'radiwa6602@dwakm.com';
    protected $apiKey  = '2b98fda4b8c22b26d7da69d816bf3ae7';

    protected $campusId = 137882;

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
            ])->get('https://churchmetrics.com/api/v1/service_times.json');

            if ($response->successful()) {
                return collect($response->json());
            } else {
                Log::warning('ServiceTimes API Failed', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return collect();
            }
        } catch (\Exception $e) {
            Log::error('ServiceTimes API Error: ' . $e->getMessage());
            return collect();
        }
    }

   public function create(array $data)
{
    try {
        $campusId = $data['campus_id']; 

        $response = Http::withHeaders([
            'X-Auth-User' => $this->apiUser,
            'X-Auth-Key'  => $this->apiKey,
            'Accept'      => 'application/json',
        ])->post('https://churchmetrics.com/api/v1/service_times.json', [
            'campus_id'          => $campusId,
            'day_of_week'        => $data['day_of_week'],
            'time_of_day'        => date('Y-m-d\TH:i:s\Z', strtotime($data['time_of_day'])),
            'timezone'           => $data['timezone'] ?? 'Central Time (US & Canada)',
            'relation_to_sunday' => $data['relation_to_sunday'] ?? 'Current',
            'date_start'         => $data['date_start'],
            'date_end'           => $data['date_end'],
            'replaces'           => $data['replaces'] ?? false,
            'event_id'           => $data['event_id'] ?? null,
        ]);

        if ($response->successful()) {
            return ['success' => true, 'service_time' => $response->json()];
        } else {
            Log::error('Create ServiceTime API Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return ['success' => false, 'message' => 'Failed to create service time.'];
        }
    } catch (\Exception $e) {
        Log::error('Create ServiceTime API Error: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


    public function updateServiceTimeOnAPI($cm_id, array $data)
{
    try {

        // Same time formatting as create()
        $timeFormatted = date('Y-m-d\TH:i:s\Z', strtotime($data['time_of_day']));

        $payload = [
            'campus_id'          => $data['campus_id'],
            'day_of_week'        => $data['day_of_week'],
            'time_of_day'        => $timeFormatted,
            'timezone'           => $data['timezone'] ?? 'Central Time (US & Canada)',
            'relation_to_sunday' => $data['relation_to_sunday'] ?? 'Current',
            'date_start'         => $data['date_start'],
            'date_end'           => $data['date_end'],
            'replaces'           => $data['replaces'] ?? false,
            'event_id'           => $data['event_id'] ?? null,
        ];

        $response = Http::withHeaders([
            'X-Auth-User' => $this->apiUser,
            'X-Auth-Key'  => $this->apiKey,
            'Accept'      => 'application/json',
        ])->put("https://churchmetrics.com/api/v1/service_times/{$cm_id}.json", $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'service_time' => $response->json()
            ];
        }

        Log::error('Update ServiceTime API Failed', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return ['success' => false, 'message' => 'Failed to update service time.'];

    } catch (\Exception $e) {

        Log::error('Update ServiceTime API Error: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

}
