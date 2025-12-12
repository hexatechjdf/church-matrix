<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManageServiceTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     public $records;
     public $is_saved;
     public $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($records,$user_id,$is_saved = true)
    {
        $this->records = $records;
        $this->is_saved = $is_saved;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->records;
        $table = $this->is_saved;
        $final = collect($data)->map(function ($record) {
            $time = @$record['time_of_day'] ? Carbon::parse(@$record['time_of_day'])->format('H:i:s') : null;
            return [
                'cm_id'                    => @$record['id'],
                'day_of_week'    => @$record['day_of_week'],
                'complete_time'    => @$record['time_of_day'],
                'time_of_day'            => $time,
                'timezone'            => @$record['timezone'],
                'relation_to_sunday'            => @$record['relation_to_sunday'],
                'date_start'         => @$record['date_start'],
                'date_end'          => @$record['date_end'],
                'replaces'                     => @$record['replaces'],
                'event_id'           => @$record['event']['id'] ?? null,
                'event_name'           => @$record['event']['name'] ?? null,
                'campus_name'          => @$record['campus']['slug'],
                'campus_id'          => @$record['campus']['id'],
                'created_at'                => now(),
                'updated_at'                => now(),
            ];
        })->toArray();


        if(!$table)
        {
            $key = "service_time_temp_{$this->user_id}";
            $existing = cache()->get($key, []);
            foreach ($final as $record) {
                $existing[] = $record;
            }
            cache()->put($key, $existing, 3600);
        }else{
            $this->saveRecords($final);
        }

    }

    public function saveRecords($final)
    {
        $existingIds = DB::table('service_times')
            ->whereIn('cm_id', array_column($final, 'cm_id'))
            ->pluck('cm_id')
            ->toArray();

        $toInsert = array_filter($final, function ($record) use ($existingIds) {
            return !in_array($record['cm_id'], $existingIds);
        });

        if (!empty($toInsert)) {
            DB::table('service_times')->insert($toInsert);
        }

    }
}
