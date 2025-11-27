<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ManageCampusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $current_user = $this->user;
        $c = Campus::where('user_id',$current_user->id)->first();
        if(!$c)
        {
            $timezone = get_setting($current_user->id, 'timezone') ?? 'London';
            $region = get_setting($current_user->id, 'region') ?? '1';
            $data = [
                "slug" => $current_user->email,
                "description" => $current_user->email,
                "timezone" => $timezone,
                "active" => true,
                "region_id" => $region,
            ];
            $res = $this->request('POST', 'campuses.json', $data);
            if($res && $res->id)
            {
                $data['user_id'] = $current_user->id;
                $data['location_id'] = $current_user->location;
                $data['campus_unique_id'] = $res->id;
                $c = Campus::create($data);
            }

        }
    }

    public function request($method, $url, $data = [])
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

            return $response->successful() ? $response->json() : false;

        } catch (\Exception $e) {
            \Log::error("ChurchMetrics API error: ".$e->getMessage());
            return false;
        }
    }

}
