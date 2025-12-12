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
        $id    = $this->user_id;
        $table = $this->is_saved;
        $page  = $this->page;

        $crm = \getChurchToken(null,$id);

        try {
            $nextPage = $this->processPage($page, $churchService,$crm,$table,$id);
            if ($nextPage) {
                if ($table) {
                    dispatch(new self($id, $table ,$nextPage));
                } else {
                    dispatch_sync(new self($id, $table ,$nextPage));
                }
            } else if (!$table) {
                $key = "service_time_temp_$id";
                $all = cache()->get($key, []);

                return $all;
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }

    }

    public function processPage($page, $churchService,$crm,$table,$id,$url = 'service_times.json',$perpage = 3)
    {
        $params = [
            'page'      => $page,
            'per_page'  => $perpage,
        ];

        list($data, $linkHeader) = $churchService->request('GET', $url, $params, true,$crm);

        if (!$data) {
            return null;
        }

        dispatch_sync(new ManageServiceTimeJob($data,$id,$table));
        // if ($table) {
        //     dispatch(new ManageServiceTimeJob($data,$id));
        // } else {
        //     dispatch_sync(new ManageServiceTimeJob($data,$id,false));
        // }

        $l = @$linkHeader[0] ?? null;

        $pages = \parseLinks($l);

        return @$pages['next'] ?: null ;
    }
}
