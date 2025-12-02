<?php

namespace App\Jobs\churchmatrix;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ManageRecordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $records;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($records)
    {
         $this->records = $records;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->records;
        $final = collect($data)->map(function ($record) {
            list($year, $week) = $this->decodeWeekReference($record['week_reference']);
            \Log::info($year);
            return [
                'record_unique_id'          => @$record['id'],
                'organization_unique_id'    => @$record['organization_id'],
                'week_reference'            => @$record['week_reference'],
                'week_no'                   => @$week,
                'week_volume'               => @$year . '_' . @$week,
                'service_date_time'         => @$record['service_date_time'],
                'service_timezone'          => @$record['service_timezone'],
                'value'                     => @$record['value'],
                'service_unique_time_id'    => @$record['service_time_id'],
                'event_unique_id'           => @$record['event']['id'] ?? null,
                'category_unique_id'        => @$record['category']['id'] ?? null,
                'campus_unique_id'          => @$record['campus']['id'],
                'record_created_at'         => @$record['created_at'],
                'record_updated_at'         => @$record['updated_at'],
                'created_at'                => now(),
                'updated_at'                => now(),
            ];
        })->toArray();

        $this->saveRecords($final);
    }

    public function saveRecords($final)
    {
        $existingIds = DB::table('church_records')
            ->whereIn('record_unique_id', array_column($final, 'record_unique_id'))
            ->pluck('record_unique_id')
            ->toArray();

        $toInsert = array_filter($final, function ($record) use ($existingIds) {
            return !in_array($record['record_unique_id'], $existingIds);
        });

        if (!empty($toInsert)) {
            DB::table('church_records')->insert($toInsert);
        }
    }

    public function decodeWeekReference($week_reference)
    {
        $baseYear = 1970;
        $year = $baseYear + intdiv($week_reference, 52);
        $week = $week_reference % 52;
        if ($week === 0) $week = 52;

        return [$year, $week];
    }
}
