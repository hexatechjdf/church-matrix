<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ChurchService;

class ServiceTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user_id;
    public $is_saved;
    public $page;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$is_saved = false,$page = 1)
    {
        $this->is_saved = $is_saved;
        $this->user_id = $user_id;
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChurchService $churchService)
    {
        $id = $this->user_id;
        $table = $this->is_saved;
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

    public function processPage($campus_id, $page, $churchService,$url = 'records.json',$perpage = 100)
    {
        $params = [
            'page'      => $page,
            'per_page'  => 2,
        ];

        $url = "records.json";
        list($data, $linkHeader) = $churchService->request('GET', $url, $params, true);

        if (!$data) {
            return null;
        }

        dispatch((new ManageRecordsJob($data)))->delay(5);
        $l = @$linkHeader[0] ?? null;

        $pages = \parseLinks($l);

        return @$pages['next'] ?: null ;
    }
}
