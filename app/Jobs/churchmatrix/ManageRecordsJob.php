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

class ManageRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $records;
    public $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($records,$user_id)
    {
        $this->records = $records;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
   public function handle(ChurchService $churchService)
    {
        $data = $this->records;
        $user_id = $this->user_id;

        $final = collect($data)->map(function ($record) use ($churchService, $user_id) {
            return $churchService->setRecordData($user_id, $record);
        })->toArray();

        $this->saveRecords($final);
    }

    public function saveRecords($final)
    {
        DB::table('church_records')->upsert(
            $final,
            ['record_unique_id'],
            [
                'user_id',
                'organization_unique_id',
                'week_reference',
                'week_no',
                'week_volume',
                'service_date_time',
                'service_timezone',
                'value',
                'service_unique_time_id',
                'event_unique_id',
                'event_name',
                'category_unique_id',
                'category_name',
                'campus_unique_id',
                'campus_name',
                'record_created_at',
                'record_updated_at',
                'updated_at'
            ]
        );

        // $existingIds = DB::table('church_records')
        //     ->whereIn('record_unique_id', array_column($final, 'record_unique_id'))
        //     ->pluck('record_unique_id')
        //     ->toArray();

        // $toInsert = array_filter($final, function ($record) use ($existingIds) {
        //     return !in_array($record['record_unique_id'], $existingIds);
        // });

        // if (!empty($toInsert)) {
        //     DB::table('church_records')->insert($toInsert);
        // }
    }


}
