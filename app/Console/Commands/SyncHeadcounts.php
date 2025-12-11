<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlanningService;
use App\Models\CrmToken;
use App\Models\Headcount;
use Carbon\Carbon;

class SyncHeadcounts extends Command
{
    protected $signature = 'headcounts:sync {--created=} {--updated=}';

    protected $description = '';

    public function handle(PlanningService $service)
    {
        $createdDate = $this->option('created');
        $updatedDate = $this->option('updated');

        if (!$createdDate && !$updatedDate) {
            $this->error('Please provide created_at or updated_at');
            return Command::FAILURE;
        }

        $token = CrmToken::where('id', 5)->value('access_token');
        if (!$token) {
            $this->error('Token not found!');
            return Command::FAILURE;
        }

        $response = $service->getHeadcounts($createdDate, $updatedDate, $token);
        // dd($response);

        if (empty($response->data)) {
            $this->warn('No headcounts found!');
            return Command::SUCCESS;
        }

        $included  = collect($response->included)->keyBy(function ($item) {
        return $item->type . '.' . $item->id;  // "Event.123"
         });
         $events = $included->where('type','EventTime')->toArray();
         $included = $included->toArray();
        
        $count = 0;
        $data= [];
        foreach ($response->data as $item) {
            $eventTime= $item->relationships->event_time->data->id;
            $attendanceType= $item->relationships->attendance_type->data->id;
            $event = $events['EventTime.'.$eventTime];
            $data[] = [
                    'attendance_id'=>$attendanceType,
                    'value'=>$item->attributes->total,
                    'headcount_id'=>$item->id,
                    'event_id'=> $event->relationships->event->data->id,
                    'event_time_id'=>$eventTime,
                    'service_time'=>$event->attributes->starts_at,
                    'week_reference'=>null,

                ];
            // Headcount::updateOrCreate(
            //     ['headcount_id' => $item->id],
            //     [
            //         'total' => $item->attributes->total,
            //         'headcount_created_at' => $item->attributes->created_at
            //             ? Carbon::parse($item->attributes->created_at)->format('Y-m-d H:i:s')
            //             : null,
            //         'headcount_updated_at' => $item->attributes->updated_at
            //             ? Carbon::parse($item->attributes->updated_at)->format('Y-m-d H:i:s')
            //             : null,
            //     ]
            // );
            $count++;
        }
        
        // $data = [];
        foreach($events as $event){
            $attrib = $event->attributes;
            $values = [
                'regular'=>$attrib->regular_count??0,
                'guest'=>$attrib->guest_count??0,
                'volunteer'=>$attrib->volunteer_count??0,
            ];
            
            foreach($values as $v=>$total){
                if($total<=0){
                    continue;
                }
                $data[] = [
                    'attendance_id'=>$v,
                    'value'=>$total,
                    'headcount_id'=>$v,
                    'event_id'=>$event->relationships->event->data->id,
                    'event_time_id'=>$event->id,
                    'service_time'=>$attrib->starts_at,
                    'week_reference'=>null,

                ];
            }
           
        }
        dd($data);

        $this->info("Sync complete! {$count} headcounts saved.");
    }
}

    