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
        $data = $request->only([
            'day_of_week',
            'time_of_day',
            'date_start',
            'date_end',
            'replaces',
            'event_id',
        ]);

        $result = $this->service->update($id, $data);

        if ($result['success']) {
    // Normalize: if the API returns ['service_time' => {...}], unwrap it
    $serviceTime = $result['service_time']['service_time'] ?? $result['service_time'];

    // Optionally map the fields you need to simplify front-end handling
    $serviceTimeNormalized = [
        'id'           => $serviceTime['id'] ?? null,
        'campus_id'    => $serviceTime['campus_id'] ?? 137882, // default if missing
        'day_of_week'  => $serviceTime['day_of_week'] ?? 0,
        'time_of_day'  => $serviceTime['time_of_day'] ?? '',
        'date_start'   => $serviceTime['date_start'] ?? null,
        'date_end'     => $serviceTime['date_end'] ?? null,
        'replaces'     => $serviceTime['replaces'] ?? false,
        'event_id'     => $serviceTime['event_id'] ?? null,
        'event'        => $serviceTime['event'] ?? null,
        'timezone'     => $serviceTime['timezone'] ?? null,
        'relation_to_sunday' => $serviceTime['relation_to_sunday'] ?? null,
    ];

    return response()->json([
        'success' => true,
        'service_time' => $serviceTimeNormalized,
        'message' => 'Service time updated successfully!'
    ]);
}

}}
