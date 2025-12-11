<?php

namespace App\Services;

use Carbon\Carbon;

class EventDataFormatter
{
    /**
     * Format event data for upsert
     *
     * @param object $item
     * @param object|null $headcount
     * @param int|string $userId
     * @param string $locationId
     * @param \Carbon\Carbon $startsAt
     * @param string $eventName
     * @return array
     */
    public static function format($item, $headcount, $userId, $locationId, $startsAt, $eventName)
    {
        return [
            'event_time_id'        => (string)$item->id,
            'headcount_id'         => $headcount?->id,
            'user_id'              => $userId,
            'event_id'             => $item->relationships->event->data->id ?? null,
            'location_id'          => $locationId,
            'event_name'           => $eventName,
            'service_name'         => $item->attributes->name ?? null,
            'service_date'         => $startsAt->toDateString(),
            'service_time'         => $startsAt->format('H:i:s'),
            'regular_count'        => $item->attributes->regular_count,
            'guest_count'          => $item->attributes->guest_count,
            'volunteer_count'      => $item->attributes->volunteer_count,
            'attendance_id'        => $headcount?->relationships->attendance_type->data->id ?? null,
            'value'                => $headcount?->attributes->total ?? null,
            'headcount_type'       => $headcount ? 'manual' : null,
            'headcount_created_at' => $headcount?->attributes->created_at
                                        ? Carbon::parse($headcount->attributes->created_at)->format('Y-m-d H:i:s'): null,
            'headcount_updated_at' => $headcount?->attributes->updated_at
                                        ? Carbon::parse($headcount->attributes->updated_at)->format('Y-m-d H:i:s'): null,
            'is_live'              => $startsAt->isToday() && $startsAt->isFuture(),
            'synced_at'            => now(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ];
    }
}
