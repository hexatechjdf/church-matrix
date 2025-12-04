<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
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
   

    public function update(Request $request, $id)
{
    $cm_id = $id;

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

    $result = $this->service->updateServiceTimeOnAPI($cm_id, $data);

    if ($result['success'] ?? false) {
        return response()->json([
            'success' => true,
            'service_time' => $result['service_time'] ?? (object) $data,
            'message'    => 'Service Time updated successfully!'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => $result['message'] ?? 'Failed to update on ChurchMatrix.'
    ], 422);
}
}
