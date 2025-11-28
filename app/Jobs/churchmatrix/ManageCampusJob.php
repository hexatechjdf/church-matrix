<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Campus;
use Illuminate\Support\Facades\Http;


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

        $this->getRecords('137883');
        // .....................
        $current_user = $this->user;
        $c = Campus::where('user_id',$current_user->id)->first();
        if(!$c)
        {
            $timezone = get_setting($current_user->id, 'timezone') ?? 'London';
            $region = get_setting($current_user->id, 'region') ?? '8928';
            $data = [
                "slug" => $current_user->email.'2',
                "description" => $current_user->email.'3',
                "timezone" => $timezone,
                "active" => true,
                "region_id" => $region,
            ];

            // $res = $this->request('POST', 'campuses.json', $data);
            $res =  [
                        'id' => 138224,
                        'slug' => '05NesdgGmR3jhRvLBC7A@gmail.com2',
                        'description' => '05NesdgGmR3jhRvLBC7A@gmail.com3',
                        'region_id' => 8928,
                        'timezone' => 'London',
                        'active' => true,
                        'created_at' => '2025-11-28T10:39:44.373Z',
                        'updated_at' => '2025-11-28T10:39:44.373Z',
                        'code_capacity' => NULL,
                        'chair_capacity' => NULL,
                    ];
            if($res && isset($res['id']))
            {
                $data['user_id'] = $current_user->id;
                $data['location_id'] = $current_user->location;
                $data['campus_unique_id'] = $res['id'];
                $c = Campus::create($data);
            }

            // 138224

        }
    }


    public function getRecords($campus_id)
    {
        $count = 1;
        $page = 1;
        $per_page = 2;
        $all = [];


        while (true) {

            $params = [
                'campus_id' => $campus_id,
                'page'      => $page,
                'per_page'  => $per_page,
            ];
            $url = "records.json";
            list($data,$linkHeader) = $this->request('GET', $url, $params,true);

            $all = array_merge($all, $data);

            \Log::info($all);

            $l  = @$linkHeader[0] ?? null;

            if (!$l) {
                break;
            }

            $page = $this->parseLinks($l);


            if (!$page || $page == '' || empty($page)) {
                break;
            }
            $count++;

            if($count == 12)
            {
                \Log::info('setting');
                break;
            }
        }

        dd($all);

        return $all;
    }

    private function parseLinks($header)
    {
        if ($header) {
            // Split multiple links
            $links = explode(',', $header);

            $nextPage = null;

            foreach ($links as $link) {

                // Check if this part contains rel='next'
                if (strpos($link, "rel='next'") !== false) {

                    // Extract the URL inside <>
                    preg_match('/<([^>]+)>/', $link, $matches);

                    if (!empty($matches[1])) {

                        $nextUrl = $matches[1];

                        // Parse URL to extract query params
                        $query = parse_url($nextUrl, PHP_URL_QUERY);

                        parse_str($query, $params);

                        $nextPage = $params['page'] ?? null;
                    }
                }
            }

            return $nextPage;
        }
        return null;
    }


    private function getPageNumber($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $params);

        return isset($params['page']) ? intval($params['page']) : null;
    }





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

            \Log::info($r);

            return $header_required ? [$r,@$response->getHeader('Link')] : $r;

        } catch (\Exception $e) {
            \Log::error("ChurchMetrics API error: ".$e->getMessage());
            return false;
        }
    }

    // return collect($apiResponse['errors'] ?? [])
    // ->flatten()
    // ->contains("has already been used.");

}
