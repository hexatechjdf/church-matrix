<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlanningService;
use App\Models\CrmToken;
use App\Models\EventsData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEventsData extends Command
{
    private const PER_PAGE = 100;
    private const BATCH_SIZE = 500;

    protected $signature = 'events_data:sync
    {--user= : Sync for a specific user ID}
    {--location= : Sync for a specific location ID} {--created=} {--updated=} {--all} {--location=} {--offset=}';

    protected $description = '';

    private PlanningService $planningService;
    private int $totalSynced = 0;

    public function __construct(PlanningService $planningService)
    {
        parent::__construct();
        $this->planningService = $planningService;
    }

    public function handle(): int
    {


        $tokens = $this->getTokensQuery();
        if ($tokens->isEmpty()) {
            $this->error('No Planning Center connection found!');
            return Command::FAILURE;
        }

        $this->info("Starting sync for {$tokens->count()} connection(s)...");
        $progressBar = $this->output->createProgressBar($tokens->count());

        foreach ($tokens as $crm) {
            if (!$this->validateToken($crm)) {
                continue;
            }

            try {
                $synced = $this->syncUserEvents($crm, $this->options());
                $this->totalSynced += $synced;
            } catch (\Exception $e) {
                $this->error("Failed for User {$crm->user_id}: {$e->getMessage()}");
                Log::error('Event sync failed', [
                    'user_id' => $crm->user_id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✓ TOTAL: {$this->totalSynced} records synced across all churches!");

        return Command::SUCCESS;
    }

    private function getTokensQuery()
    {
        $query = CrmToken::where('crm_type', 'planning');

        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        if ($locationId = $this->option('location')) {
            $query->where('location_id', $locationId);
        }

        return $query->get();
    }

    private function getWeekReference(Carbon $date): string
    {
        return $date->format('o-W');
    }



    private function validateToken(CrmToken $crm): bool
    {
        if (!$crm->access_token || !$crm->user_id) {
            $this->warn("User ID {$crm->user_id} missing token — skipped");
            return false;
        }

        return true;
    }

    private function syncUserEvents(CrmToken $crm, $options = []): int
    {
        $userId = $crm->user_id;
        $locationId = $crm->location_id ?? 'unknown';
        $syncedCount = 0;
        $offset = $options['offset'] ?? 0;
        $type = $options['type'] ?? '';
        $batch = [];

        $this->info("Syncing User: {$userId} | Location: {$locationId}");

        do {

            if ($type == 'headcount') {
                $filter = [];
                $createdat = $options['created'] ?? "";
                if ($createdat) {
                    $filter['[created_at]'] = $createdat;
                }
                $updated = $options['updated'] ?? "";
                if ($updated) {
                    $filter['[updated_at]'] = $updated;
                }
                if (count($filter) == 0) {
                    break;
                }
                $response = $this->fetchAttendances($offset, $crm->access_token, $filter);
            } else {
                $response = $this->fetchEventTimes($offset, $crm->access_token);
               
            }


            if (empty($response->data)) {
                break;
            }

            $includedMap = $this->buildIncludedMap($response->included ?? []);
            $events = $this->processEventBatch($response->data, $userId, $locationId, $includedMap, $type);

            $batch = array_merge($batch, $events);

            if (count($batch) >= self::BATCH_SIZE) {
                $this->upsertBatch($batch);
                $syncedCount += count($batch);
                $batch = [];
            }

            $offset += self::PER_PAGE;
        } while (!empty($response->links->next));

        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $syncedCount += count($batch);
        }

        $this->info("User {$userId} → {$syncedCount} services synced!");
        return $syncedCount;
    }

    private function fetchEventTimes(int $offset, string $token): object
    {
        $url = "check-ins/v2/event_times?include=event,headcounts&per_page=" . self::PER_PAGE . "&offset={$offset}";

        return $this->planningService->planning_api_call($url, 'get', '', [], false, $token);
    }

    private function fetchAttendances(int $offset, string $token, $filter): object
    {
        return $this->planningService->getHeadcounts($offset, $token, $filter);
    }

    private function buildIncludedMap(array $included): Collection
    {

        $included  = collect($included)->keyBy(function ($item) {
            return $item->type . '.' . $item->id;  // "Event.123"
        });
        return $included;
    }

    private function processEventBatch(array $records, $userId, $locationId, Collection $includedMap, $type = ''): array
    {
        $data = [];

        $events = $includedMap->where('type', 'EventTime')->toArray();
        $includedMap = $includedMap->toArray();
        if ($type == 'headcount') {
            $count = 0;
            $data = [];
            foreach ($records as $item) {
                $eventTime = $item->relationships->event_time->data->id;
                $attendanceType = $item->relationships->attendance_type->data->id;
                $event = $events['EventTime.' . $eventTime];
                $record = [
                    'attendance_id' => $attendanceType,
                    'value' => $item->attributes->total,
                    'headcount_id' => $item->id,
                    'event_id' => $event->relationships->event->data->id,
                    'event_time_id' => $eventTime,
                ];
                $startsAt = Carbon::parse($event->attributes->starts_at);
                $record = $this->mergeDateFields($record, $startsAt);
                $data[] =  $record;
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
            foreach ($events as $event) {
                $attrib = $event->attributes;
                $values = [
                    'regular' => $attrib->regular_count ?? 0,
                    'guest' => $attrib->guest_count ?? 0,
                    'volunteer' => $attrib->volunteer_count ?? 0,
                ];

                foreach ($values as $v => $total) {
                    
                    $record = [
                        'attendance_id' => $v,
                        'value' => $total,
                        'headcount_id' => $v,
                        'event_id' => $event->relationships->event->data->id,
                        'event_time_id' => $event->id,
                    ];
                    $startsAt = Carbon::parse($attrib->starts_at);
                    $record = $this->mergeDateFields($record, $startsAt);
                    $data[] =  $record;
                }
            }
        } else {
            foreach ($records as $item) {
                $startsAt = Carbon::parse($item->attributes->starts_at);
                $eventId = $item->relationships->event->data->id ?? null;
                $eventName = $includedMap['Event.' . $eventId]->attributes->name ?? 'Unknown Event';
                $headcounts = $item->relationships->headcounts->data ?? [];

                if (!empty($headcounts)) {
                    foreach ($headcounts as $hcRef) {
                        $hc = $includedMap['Headcount.' . $hcRef->id] ?? null;
                        if ($hc) {
                            $data[] = $this->buildEventData($item, $eventId, $eventName, $userId, $locationId, $hc, $startsAt);
                        }
                    }
                } else {
                    $attrib = $item->attributes;
                    $values = [
                        'regular' => $attrib->regular_count ?? 0,
                        'guest' => $attrib->guest_count ?? 0,
                        'volunteer' => $attrib->volunteer_count ?? 0,
                    ];
                    foreach ($values as $v => $total) {
                        
                        $item->key = $v;
                        $item->total = $total;
                        $data[] = $this->buildEventData($item, $eventId, $eventName, $userId, $locationId, null, $startsAt);
                    }
                }
            }
        }



       
        return $data;
    }
    private function mergeDateFields($data, Carbon $startsAt)
    {
        $newData = [
            'week_reference' => $this->getWeekReference($startsAt),
            'service_date'  => $startsAt->toDateString(),
            'service_time'  => $startsAt->format('H:i:s'),
        ];
        return array_merge($data, $newData);
    }
    private function buildEventData($item, $eventId, $eventName, $userId, $locationId, $headcount, Carbon $startsAt): array
    {
        $createdAt  = $headcount ? $headcount->attributes->created_at : $item->attributes->starts_at;
        $updatedAt  = $headcount ? $headcount->attributes->updated_at : $item->attributes->starts_at;
        $record = [
            'event_time_id' => (string)$item->id,
            'headcount_id'  => $headcount->id ?? null,
            'user_id'       => $userId,
            'event_id'      => $eventId,
            'event_name'    => $eventName,
            'service_name'  => $item->attributes->name ?? $eventName,

            'attendance_id' => $headcount ? ($headcount->relationships->attendance_type->data->id ?? null) : $item->key,
            'value'         => $headcount ? ($headcount->attributes->total ?? 0) : $item->total,
            'headcount_type' => $headcount ? 'manual' : null,
            'headcount_created_at' => ($createdAt)
                ? Carbon::parse($createdAt)->format('Y-m-d H:i:s')
                : null,
            'headcount_updated_at' => ($updatedAt ?? null)
                ? Carbon::parse($updatedAt)->format('Y-m-d H:i:s')
                : null,
            'location_id'   => $locationId,
            'synced_at'     => now(),
        ];
        $record = $this->mergeDateFields($record, $startsAt);
        return $record;
    }

    private function upsertBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            EventsData::upsert(
                $batch,
                ['event_time_id', 'headcount_id', 'user_id'],
                [
                    'event_id',
                    'event_name',
                    'service_name',
                    'week_reference',
                    'service_date',
                    'service_time',
                    'attendance_id',
                    'value',
                    'headcount_type',
                    'headcount_created_at',
                    'headcount_updated_at',
                    'location_id',
                    'synced_at'
                ]
            );

            EventsData::where('value',0)->delete();
        });
    }
}
