<?php

namespace App\Jobs\Planning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Services\PlanningService;

class RefreshPlanningCenterToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $refreshToken;
    protected $userId;

    public function __construct($refreshToken, $userId)
    {
        $this->refreshToken = $refreshToken;
        $this->userId = $userId;
    }

    public function handle(PlanningService $planningService)
    {
        $planningService->fetchPlanningToken($this->refreshToken,$this->userId,'refresh_token');
    }


}
