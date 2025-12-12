<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Campus;
use App\Models\User;
use App\Models\CrmToken;
use App\Services\ChurchService;
use Illuminate\Support\Facades\Http;
use App\Jobs\churchmatrix\GetRecordsCampusJob;


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
    public function handle(ChurchService $churchService)
    {
        $current_user = $this->user;
        if(!$current_user)
        {
            return;
        }

        $c = Campus::where('user_id',$current_user->id)->first();
        if(!$c)
        {
            $crm  = \getChurchToken(null,$current_user->id);

            $timezone = $current_user->timezone ?? 'London';
            $region = @$crm->company_id ?? '8928';
            $data = [
                "slug" => $current_user->email,
                "description" => $current_user->email,
                "timezone" => $timezone,
                "active" => true,
                "region_id" => $region,
            ];

            $res = $churchService->request('POST', 'campuses.json', $data,false,$crm);

            if($res && isset($res['id']))
            {
                $data['user_id'] = $current_user->id;
                $data['location_id'] = $current_user->location;
                $data['campus_unique_id'] = $res['id'];
                $data['name'] = $data['slug'];
                $c = Campus::create($data);

                // dispatch((new GetRecordsCampusJob($c->campus_unique_id,$current_user->id)))->delay(5);
            }
        }
    }

}
