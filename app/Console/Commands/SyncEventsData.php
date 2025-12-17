<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlanningService;
use App\Models\CrmToken;
use App\Models\EventsData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEventsData extends Command
{
    private const PER_PAGE = 100;
    private const BATCH_SIZE = 500;

    protected $signature = 'events_data:sync
    {--user= : Sync for a specific user ID}
    {--location= : Sync for a specific location ID}';

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
                $synced = $this->syncUserEvents($crm);
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

    private function validateToken(CrmToken $crm): bool
    {
        if (!$crm->access_token || !$crm->user_id) {
            $this->warn("User ID {$crm->user_id} missing token — skipped");
            return false;
        }

        return true;
    }

    private function syncUserEvents(CrmToken $crm): int
    {
        $userId = $crm->user_id;
        $locationId = $crm->location_id ?? 'unknown';
        $syncedCount = 0;
        $offset = 0;
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
            $events = $this->processEventBatch($response->data, $userId, $locationId, $includedMap);

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

    private function buildIncludedMap(array $included): array
    {
        $map = [];
        foreach ($included as $item) {
            $map[$item->type][$item->id] = $item;
        }
        return $map;
    }

    private function processEventBatch(array $data, $userId, $locationId, array $includedMap): array
    {
        $events = [];

        foreach ($data as $item) {
            $startsAt = Carbon::parse($item->attributes->starts_at);
            $eventId = $item->relationships->event->data->id ?? null;
            $eventName = $includedMap['Event'][$eventId]->attributes->name ?? 'Unknown Event';
            $headcounts = $item->relationships->headcounts->data ?? [];

            if (!empty($headcounts)) {
                foreach ($headcounts as $hcRef) {
                    $hc = $includedMap['Headcount'][$hcRef->id] ?? null;
                    if ($hc) {
                        $events[] = $this->buildEventData($item, $eventId, $eventName, $userId, $locationId, $hc, $startsAt);
                    }
                }
            } else {
                $events[] = $this->buildEventData($item, $eventId, $eventName, $userId, $locationId, null, $startsAt);
            }
        }

        return $events;
    }

    private function buildEventData($item, $eventId, $eventName, $userId, $locationId, $headcount, Carbon $startsAt): array
    {
        return [
            'event_time_id' => (string)$item->id,
            'headcount_id'  => $headcount->id ?? null,
            'user_id'       => $userId,
            'event_id'      => $eventId,
            'event_name'    => $eventName,
            'service_name'  => $item->attributes->name ?? $eventName,
            'week_reference' => $item->attributes->week_reference ?? null,
            'service_date'  => $startsAt->toDateString(),
            'service_time'  => $startsAt->format('H:i:s'),
            'attendance_id' => $headcount->relationships->attendance_type->data->id ?? null,
            'value'         => $headcount->attributes->total ?? 0,
            'headcount_type' => $headcount ? 'manual' : null,
            'headcount_created_at' => $headcount->attributes->created_at ?? null
                ? Carbon::parse($headcount->attributes->created_at)->format('Y-m-d H:i:s')
                : null,
            'headcount_updated_at' => $headcount->attributes->updated_at ?? null
                ? Carbon::parse($headcount->attributes->updated_at)->format('Y-m-d H:i:s')
                : null,
            'location_id'   => $locationId,
            'synced_at'     => now(),
        ];
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
        });
    }
}
