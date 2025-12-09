<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use App\Models\ServiceTime;
use App\Services\ChurchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceTimeController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $campuses = $this->service->fetchCampuses();
        $events = $this->service->fetchEvents();

        return view('locations.churchmatrix.service_times.index',compact('events','campuses'));
    }

    public function getTimes(Request $request)
    {
        try {

            $page     = $request->page ?? 1;
            $perPage  = $request->per_page ?? 10;

            $cacheKey = "service_timessss_{$campusId}_page_{$page}";

             $params = [
                    'page'      => $page,
                    'per_page'  => $perPage,
                ];

                dd($params);

            $apiData = Cache::remember($cacheKey, 600, function () use ($campusId, $page, $perPage) {

                $params = [
                    'campus_id' => (int)$campusId,
                    'page'      => $page,
                    'per_page'  => $perPage,
                ];

                dd($params);

                $url = "service_times.json";

                list($serviceTimes, $linkHeader) = $this->service->request('GET', $url, $params, true);

                $l = @$linkHeader[0] ?? null;
                $pages = \parseLinks($l,$params);

                return [
                    'items' => collect($serviceTimes)->map(function ($t) use ($pages) {
                        return [
                            'id'         => $t['id'],
                            'campus'     => $t['campus']['slug'] ?? $t['campus_id'] ?? 'N/A',
                            'day'        => $t['day_of_week'] ?? 'N/A',
                            'time'       => $t['time_of_day'] ?? 'N/A',
                            'timezone'   => $t['timezone'] ?? 'N/A',
                            'relation'   => $t['relation_to_sunday'] ?? 'N/A',
                            'date_start' => $t['date_start'] ?? 'N/A',
                            'date_end'   => $t['date_end'] ?? 'N/A',
                            'next'       => $pages['next'] ?? null,
                            'prev'       => $pages['prev'] ?? null,
                        ];
                    })->toArray(),

                    'next' => $pages['next'] ?? null,
                    'prev' => $pages['prev'] ?? null
                ];

            });

            return response()->json([
                "data" => $apiData['items'],
                "next" => $apiData['next'],
                "prev" => $apiData['prev']
            ]);

        } catch (\Exception $e) {
            \Log::error("Service Times API Error: ". $e->getMessage());

            return response()->json([
                "data" => [],
                "next" => null,
                "prev" => null,
            ]);
        }
    }

    public function manage(Request $request)
    {
        $data = $request->all();

        $body = [
            'day_of_week'        => $data['day_of_week'],
            'time_of_day'        => date('Y-m-d\TH:i:s\Z', strtotime($data['time_of_day'])),
            'date_start'         => $data['date_start'] ?? null,
            'date_end'           => $data['date_end'] ?? null,
            'event_id'           => $data['event_id'] ?? null,
            'campus_id'          => $data['campus_id'],
            'timezone'           => getUserTimeZone(),
        ];

        $url = $request->service_time_id ? 'service_times/'.$request->service_time_id.'.json' : 'service_times.json';
        $method = $request->id ? 'PUT' : 'POST';

        list($data, $apiEvents) = $this->service->request($method, $url, $body, true);

        dd($data);


        return response()->json([
            'success' => true,
            'service_time' => $result['service_time'],
            'message' => 'Service time created successfully!'
        ]);
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
