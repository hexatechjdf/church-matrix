<?php

namespace App\Jobs\Planning;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PlanningService;
use App\Models\CrmToken;
use App\Models\EventsData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEventsDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function handle(PlanningService $planningService)
    {
        $token = $this->options['token'] ?? null;
        $userId = $this->options['user'] ?? null;
        $options = $this->options;

        if (!$token) {
            $tokens = $this->getTokensQuery($options);
            if ($tokens->isEmpty()) {
                Log::error('No Planning Center connection found!');
                return;
            }

            foreach ($tokens as $crm) {
                if (!$this->validateToken($crm)) {
                    continue;
                }

                try {
                    $this->syncUserEvents($crm, $options, $planningService);
                } catch (\Exception $e) {
                    Log::error('Event sync failed', [
                        'user_id' => $crm->user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } else {
            try {
                $this->syncUserEvents($token, $options, $planningService);
            } catch (\Exception $e) {
                Log::error('Event sync failed', [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function getTokensQuery(array $options)
    {
        $query = CrmToken::where('crm_type', 'planning');

        if ($userId = $options['user'] ?? null) {
            $query->where('user_id', $userId);
        }

        if ($locationId = $options['location'] ?? null) {
            $query->where('location_id', $locationId);
        }

        return $query->get();
    }

    private function validateToken(CrmToken $crm): bool
    {
        if (!$crm->access_token || !$crm->user_id) {
            Log::error("User ID {$crm->user_id} missing token — skipped");
            return false;
        }

        return true;
    }

    private function syncUserEvents($tokenOrCrm, array $options, PlanningService $planningService)
    {
        $userId = $options['user'] ?? ($tokenOrCrm->user_id ?? '');
        $locationId = $tokenOrCrm->location_id ?? 'unknown';
        $token = $options['token'] ?? ($tokenOrCrm->access_token ?? '');
        $offset = (int)($options['offset'] ?? 0);
        $type = $options['type'] ?? '';
        $batch = [];
        $syncedCount = 0;

        Log::info("Syncing User: {$userId}");
        $planningService->setUserToken($userId,$token);
        $response = $this->fetchData($offset, $token, $type, $options, $planningService);
        if (empty($response) || !is_object($response) || empty($response->data)) {
            Log::info("No data received for offset {$offset}");
            return 0;
        }

        $includedMap = $this->buildIncludedMap($response->included ?? []);
        $events = $this->processEventBatch($response->data, $userId, $locationId, $includedMap, $type);
        $batch = array_merge($batch, $events);

        if (count($batch) >= 500) {
            $this->upsertBatch($batch);
            $syncedCount += count($batch);
            $batch = [];
        }

        $nextOffset = $offset + 100;
        $hasNextPage = !empty($response->links->next) && count($response->data) >= 100;

        if ($hasNextPage) {
            Log::info("Fetching next page with offset: {$nextOffset}");
            SyncEventsDataJob::dispatch(array_merge($options, ['offset' => $nextOffset]));
        }

        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $syncedCount += count($batch);
        }

        Log::info("User {$userId} → {$syncedCount} services synced!");
        return $syncedCount;
    }

    private function fetchData($offset, string $token, string $type, array $options, PlanningService $planningService): object
    {
        if ($type === 'headcount') {
            $filter = [];
            if ($createdAt = $options['created'] ?? null) {
                $filter['[created_at]'] = $createdAt.'T00:00:00Z';
            }
            if ($updatedAt = $options['updated'] ?? null) {
                $filter['[updated_at]'] = $updatedAt.'T00:00:00Z';;
            }
            return $this->fetchAttendances($offset, $token, $filter, $planningService);
        }

        return $this->fetchEventTimes($offset, $token, $planningService);
    }

    private function fetchEventTimes(int $offset, string $token, PlanningService $planningService): object
    {

        $url = "check-ins/v2/event_times?include=event,headcounts&per_page=100&offset={$offset}";
        $response = $planningService->planning_api_call($url, 'get', '', [], false,$token);

        return $response ?? (object)[
            'data' => [],
            'included' => [],
            'links' => (object)['next' => null],
            'meta' => (object)[]
        ];
    }

    private function fetchAttendances(int $offset, string $token, array $filter, PlanningService $planningService): object
    {
        $response = $planningService->getHeadcounts($offset, $token, $filter);

        return is_string($response) ? (object)[
            'data' => [],
            'included' => [],
            'links' => (object)[],
            'meta' => (object)[]
        ] : $response;
    }

    private function buildIncludedMap(array $included): Collection
    {
        return collect($included)->keyBy(fn($item) => "{$item->type}.{$item->id}");
    }

    private function processEventBatch(array $records, string $userId, string $locationId, Collection $includedMap, string $type): array
    {
        $data = [];
        $events = $includedMap->where('type', 'EventTime')->toArray();

        foreach ($records as $item) {
            if ($type === 'headcount') {
                $data = array_merge($data, $this->processHeadcount($item, $events, $includedMap));
            } else {
                $data = array_merge($data, $this->processEventTime($item, $userId, $locationId, $includedMap));
            }
        }

        return $data;
    }

    private function processHeadcount(object $item, array $events, array $includedMap): array
    {
        $data = [];
        $eventTime = $item->relationships->event_time->data->id;
        $event = $events["EventTime.{$eventTime}"] ?? null;

        if ($event) {
            $record = [
                'attendance_id' => $item->relationships->attendance_type->data->id,
                'value' => $item->attributes->total,
                'headcount_id' => $item->id,
                'event_id' => $event->relationships->event->data->id,
                'event_time_id' => $eventTime,
            ];
            $startsAt = Carbon::parse($event->attributes->starts_at);
            $data[] = $this->mergeDateFields($record, $startsAt);
        }

        return $data;
    }

    private function processEventTime(object $item, string $userId, string $locationId, array $includedMap): array
    {
        $data = [];
        $eventId = $item->relationships->event->data->id ?? null;
        $eventName = $includedMap["Event.{$eventId}"]->attributes->name ?? 'Unknown Event';
        $startsAt = Carbon::parse($item->attributes->starts_at);
        $headcounts = $item->relationships->headcounts->data ?? [];

        if (!empty($headcounts)) {
            foreach ($headcounts as $hcRef) {
                $hc = $includedMap["Headcount.{$hcRef->id}"] ?? null;
                if ($hc) {
                    $data[] = $this->buildEventData($item, $eventId, $eventName, $userId, $locationId, $hc, $startsAt);
                }
            }
        } else {
            $values = [
                'regular' => $item->attributes->regular_count ?? 0,
                'guest' => $item->attributes->guest_count ?? 0,
                'volunteer' => $item->attributes->volunteer_count ?? 0,
            ];

            foreach ($values as $type => $total) {
                $data[] = $this->buildEventData($item, $eventId, $eventName, $userId, $locationId, null, $startsAt, $type, $total);
            }
        }

        return $data;
    }

    private function buildEventData(
        object $item,
        ?string $eventId,
        string $eventName,
        string $userId,
        string $locationId,
        ?object $headcount,
        Carbon $startsAt,
        ?string $type = null,
        ?int $total = null
    ): array {
        $createdAt = $headcount ? $headcount->attributes->created_at : $item->attributes->starts_at;
        $updatedAt = $headcount ? $headcount->attributes->updated_at : $item->attributes->starts_at;

        $record = [
            'event_time_id' => (string)$item->id,
            'headcount_id' => $headcount->id ?? null,
            'user_id' => $userId,
            'event_id' => $eventId,
            'event_name' => $eventName,
            'service_name' => $item->attributes->name ?? $eventName,
            'attendance_id' => $headcount ? ($headcount->relationships->attendance_type->data->id ?? null) : $type,
            'value' => $headcount ? ($headcount->attributes->total ?? 0) : $total,
            'headcount_type' => $headcount ? 'manual' : null,
            'headcount_created_at' => $createdAt ? Carbon::parse($createdAt)->format('Y-m-d H:i:s') : null,
            'headcount_updated_at' => $updatedAt ? Carbon::parse($updatedAt)->format('Y-m-d H:i:s') : null,
            'location_id' => $locationId,
            'synced_at' => now(),
        ];

        return $this->mergeDateFields($record, $startsAt);
    }

    private function mergeDateFields(array $data, Carbon $startsAt): array
    {
        return array_merge($data, [
            'week_reference' => $this->getWeekReference($startsAt),
            'service_date' => $startsAt->toDateString(),
            'service_time' => $startsAt->format('H:i:s'),
        ]);
    }

    private function getWeekReference(Carbon $date): string
    {
        return $date->format('o-W');
    }

    private function upsertBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            EventsData::upsert(
                $batch,
                ['event_time_id', 'headcount_id', 'user_id'],
                [
                    'event_id', 'event_name', 'service_name', 'week_reference',
                    'service_date', 'service_time', 'attendance_id', 'value',
                    'headcount_type', 'headcount_created_at', 'headcount_updated_at',
                    'location_id', 'synced_at'
                ]
            );

            EventsData::where('value', 0)->delete();
        });
    }
}
