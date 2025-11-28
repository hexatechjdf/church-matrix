<?php

namespace App\Services;

use App\Models\ChurchApi;
use Illuminate\Support\Facades\Http;

class ChurchMatrixService
{
    public function saveApiCredentials(array $data): ChurchApi
    {
        return ChurchApi::create([
            'church_matrix_user' => $data['church_matrix_user'],
            'church_matrix_api'  => $data['church_matrix_api'],
        ]);
    }

    public function getLatestSettings(): ?ChurchApi
    {
        return ChurchApi::latest('id')->first();
    }



    public function fetchRegions(ChurchApi $settings)
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-User' => $settings->church_matrix_user,
                'X-Auth-Key'  => $settings->church_matrix_api,
            ])->get('https://churchmetrics.com/api/v1/regions.json');

            return $response->successful() ? $response->json() : false;
        } catch (\Exception $e) {
            \Log::error('ChurchMetrics API error: ' . $e->getMessage());
            return false;
        }
    }




    public function saveRegion(int $regionId): ?ChurchApi
    {
        $settings = $this->getLatestSettings();

        if (!$settings) {
            return null;
        }

        $settings->select_region = $regionId;
        $settings->save();

        return $settings;
    }

    public function saveLocation($locationId)
    {
        $settings = $this->getLatestSettings();

        if (!$settings) {
            return false;
        }

        $settings->location_id = $locationId;
        $settings->save();

        return $settings;
    }
}
