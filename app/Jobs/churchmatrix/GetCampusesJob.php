<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Services\ChurchService;

class GetCampusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $token;
    public $campus;
    public $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token,$campus = null,$type='parent')
    {
        $this->token = $token;
        $this->campus = $campus;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ChurchService $churchService)
    {
        $user_token = $this->token;
        $t = $this->type;
        if($this->campus)
        {
            dispatch(
                (new GetRecordsCampusJob(
                    $this->campus,
                    $user_token->user_id
                ))->delay(now()->addSeconds(5))
            );

            return;
        }

        $campuses = $churchService->fetchCampuses($user_token->user_id);


        if (empty($campuses)) {
            return response()->json(['message' => 'No campuses found'], 200);
        }

        $rows = collect($campuses)->map(function ($c) use ($t,$user_token) {
            return [
                'campus_unique_id' => $c['id'],
                'name'             => @$c['slug'] ?? @$c['name'] ?? null,
                'region_id'        => $c['region_id'] ?? null,
                'description'      => $c['description'] ?? null,
                'timezone'         => $c['timezone'] ?? null,
                'record_fetched'   => false,
                'type'   => $t,
                'created_by'   => $user_token->user_id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        })->toArray();

        DB::table('campuses')->upsert(
            $rows,
            ['campus_unique_id'],
            [
                'name',
                'region_id',
                'description',
                'timezone',
                'type',
                'created_by',
                'updated_at'
            ]
        );

        foreach ($rows as $c) {
            dispatch(
                (new GetRecordsCampusJob(
                    $c['campus_unique_id'],
                    $user_token->user_id
                ))->delay(now()->addSeconds(5))
            );
        }

        return response()->json([
            'status'  => true,
            'message' => 'Campuses synced & jobs dispatched successfully'
        ]);


    }
}
