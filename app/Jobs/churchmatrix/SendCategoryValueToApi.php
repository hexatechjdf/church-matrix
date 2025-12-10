<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Services\ChurchService;

class SendCategoryValueToApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    public $user_id;
    public $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data,$user_id,$id)
    {
        $this->data = $data;
        $this->user_id = $user_id;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChurchService $churchService)
    {
        $url = $this->id ? 'records/'.$this->id.'.json' : 'records.json';
        $method = $this->id ? 'PUT' : 'POST';

        list($data, $apiEvents) = $churchService->request($method, $url, $this->data, true);

        dd($data);



    }
}
