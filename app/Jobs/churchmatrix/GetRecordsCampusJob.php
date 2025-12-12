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
    public $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($cam_id,$user_id,$page = 1)
    {
        $this->cam_id = $cam_id;
        $this->page = $page;
        $this->user_id = $user_id;
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
            $nextPage = $this->processPage($campus_id, $page, $churchService,$this->user_id);
            \Log::info($nextPage);
            if ($nextPage) {
                dispatch(new self($campus_id,$this->user_id, $nextPage));
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

    }

    public function processPage($campus_id, $page, $churchService,$id,$url = 'records.json',$perpage = 100)
    {
        $params = [
            'campus_id' => $campus_id,
            'page'      => $page,
            'per_page'  => $perpage,
        ];

        $crm  = \getChurchToken(null,$id);

        list($data, $linkHeader) = $churchService->request('GET', $url, $params, true,$crm);

        if (!$data) {
            return null;
        }

        dispatch((new ManageRecordsJob($data,$id)))->delay(5);
        $l = @$linkHeader[0] ?? null;

        $pages = \parseLinks($l);

        return @$pages['next'] ?: null ;
    }

}
