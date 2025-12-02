<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ChurchService;
use App\Jobs\churchmatrix\ManageRecordsJob;

class GetRecordsCampusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cam_id;
    public $page;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($cam_id,$page = 1)
    {
        $this->cam_id = $cam_id;
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChurchService $churchService)
    {
        $campus_id = $this->cam_id;
        $page = $this->page;

        try {
            $nextPage = $this->processPage($campus_id, $page, $churchService);
            if ($nextPage) {
                dispatch(new self($campus_id, $nextPage));
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

    }

    public function processPage($campus_id, $page, $churchService)
    {
        $params = [
            'campus_id' => $campus_id,
            'page'      => $page,
            'per_page'  => 2,
        ];

        $url = "records.json";
        list($data, $linkHeader) = $churchService->request('GET', $url, $params, true);

        if (!$data) {
            return null;
        }

        dispatch((new ManageRecordsJob($data)))->delay(5);
        \Log::info('hi');

        $l = @$linkHeader[0] ?? null;

        \Log::info($l);
        $next = $this->parseLinks($l);

        return $next ?: null;
    }

    public function getRecords($campus_id,$churchService)
    {
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
            list($data,$linkHeader) = $churchService->request('GET', $url, $params,true);

            $d = [];
            if(!$data)
            {
                break;
            }

            $all = array_merge($all, $data);
            $l  = @$linkHeader[0] ?? null;
            if (!$l) {
                break;
            }
            $page = $this->parseLinks($l);
            if (!$page || $page == '' || empty($page)) {
                break;
            }
        }

        return $all;
    }

    private function parseLinks($header)
    {
        if ($header) {
            $links = explode(',', $header);
            $nextPage = null;
            foreach ($links as $link) {
                if (strpos($link, "rel='next'") !== false) {
                    preg_match('/<([^>]+)>/', $link, $matches);
                    if (!empty($matches[1])) {
                        $nextUrl = $matches[1];
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
}
