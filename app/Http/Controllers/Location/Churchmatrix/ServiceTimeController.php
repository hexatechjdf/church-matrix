<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceTimeRequest;
use App\Models\ServiceTime;
use App\Services\ServiceTimeService;
use Illuminate\Http\Request;

class ServiceTimeController extends Controller
{
    protected $service;

    public function __construct(ServiceTimeService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'campus_id',
            'day_of_week',
            'time_of_day',
            'timezone',
            'relation_to_sunday',
            'date_start',
            'date_end',
            'replaces',
            'event_id',
        ]);

        $result = $this->service->create($data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'service_time' => $result['service_time'],
                'message' => 'Service time created successfully!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to create service time.'
        ], 500);
    }

   public function update(Request $request, ServiceTime $serviceTime)
{
    // Validation (same create style)
    $data = $request->only([
        'campus_id',
        'day_of_week',
        'time_of_day',
        'timezone',
        'relation_to_sunday',
        'date_start',
        'date_end',
        'replaces',
        'event_id',
    ]);

    if (!$serviceTime->cm_id) {
        return response()->json([
            'success' => false,
            'message' => 'This service time is not synced with ChurchMetrics.',
        ], 400);
    }

    $updated = $this->service->updateServiceTimeOnAPI($serviceTime->cm_id, $data);

    if ($updated) {
        return response()->json([
            'success' => true,
            'message' => 'Service time updated successfully!',
            'service_time' => array_merge($data, [
                'id' => $serviceTime->id,
                'cm_id' => $serviceTime->cm_id,
            ]),
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Failed to update service time on ChurchMetrics.'
    ], 500);
}

}
