<?php

namespace App\Jobs\Setting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CrmToken;

class CreateTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $chunks;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chunks = [])
    {
        $this->chunks = $chunks;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $payload = $this->chunks;
            CrmToken::updateOrCreate(
                [
                    'user_id' => $payload['user_id'],
                    'crm_type' => $payload['crm_type']
                ],
                $payload
            );
        }catch(\Exception $e){
            \Log::info($e);
            dd($e);
        }

    }
}
