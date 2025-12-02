<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Campus;
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
        // $this->getRecords('137883');
         dispatch((new GetRecordsCampusJob('137883')))->delay(5);
         return;
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

            // $res = $myService->request('POST', 'campuses.json', $data);
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

                dispatch((new GetRecordsCampusJob($c->campus_unique_id)))->delay(5);
            }


            // 138224

        }
    }










    // return collect($apiResponse['errors'] ?? [])
    // ->flatten()
    // ->contains("has already been used.");

}
