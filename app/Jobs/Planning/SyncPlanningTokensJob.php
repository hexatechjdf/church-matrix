<?php

namespace App\Jobs\Planning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CrmToken;
use App\Jobs\Planning\SyncEventsDataJob;
use App\Jobs\Planning\RefreshPlanningCenterToken;

class SyncPlanningTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $options;
    protected $lastId;

    public function __construct(array $options = [], ?int $lastId = null)
    {
        $this->options = $options;
        $this->lastId = $lastId;
    }

    public function handle()
    {
        $query = CrmToken::where('crm_type', 'planning');

        if ($this->lastId) {
            $query->where('id', '>', $this->lastId);
        }

        $tokens = $query->orderBy('id')->limit(100)->get();

        if ($tokens->isEmpty()) {
            return;
        }
        $isRefreshToken = $this->options['refresh']??null;
        foreach ($tokens as $token) {
            if($isRefreshToken){
                RefreshPlanningCenterToken::dispatch($token->refresh_token,$token->user_id);
            }else{
                SyncEventsDataJob::dispatch([...$this->options,'user'=>$token->user_id,'token'=>$token->access_token]);
            }
            $this->lastId = $token->id;
        }

        self::dispatch($this->options, $this->lastId);
    }
}
