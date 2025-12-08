<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use App\Models\ChurchEvent;
use App\Services\ChurchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChurchEventController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {

        return view('locations.churchmatrix.events.index');
        dd(123);
        try {
            $campusId = $request->campus_id;
            $page     = $request->page ?? 1;
            $perPage  = 2;

            $cacheKey = "events_{$campusId}_page_{$page}";

            // Cache for 10 minutes
            $apiEvents = Cache::remember($cacheKey, 600, function () use ($campusId, $page, $perPage) {

                $params = [
                    'campus_id' => $campusId,
                    'page'      => $page,
                    'per_page'  => $perPage,
                ];

                $url = "service_times.json";
                list($data, $apiEvents) = $this->service->request('GET', $url, $params, true);

                // Parse Link Header for pagination
                $linkHeader = $data['headers']['Link'] ?? null;
                $pages = $this->parseLinks($linkHeader);

                return collect($apiEvents)->map(function ($event) use ($pages) {
                    return [
                        'id'         => $event['id'],
                        'name'       => $event['name'],
                        'next'       => $pages['next'] ?? null,
                        'prev'       => $pages['prev'] ?? null,
                        'created_at' => now(),
                    ];
                })->toArray();

            });

            // Return JSON for front-end
            return response()->json([
                'data' => $apiEvents,
                'next' => $apiEvents[0]['next'] ?? null,
                'prev' => $apiEvents[0]['prev'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'next' => null,
                'prev' => null
            ]);
        }
    }

    public function getEvents(Request $request)
    {

        $events = $this->service->fetchEvents();

        return response()->json([
            'data' => $events
        ]);
    }

    public function manage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $body = [
            'name' => $request->name,
        ];
        $url = $request->id ? 'events/'.$request->id.'.json' : 'events.json';
        $method = $request->id ? 'PUT' : 'POST';

        list($data, $apiEvents) = $this->service->request($method, $url, $body, true);

        Cache::forget('church_events');

        return response()->json([
             'success' => true,
             'message' => 'Event created on Church Metrics!',
             'event' => $data
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $res = $this->service->createEventToAPI($request->name);

        // dd($cm_id);

        if ($res) {
            return response()->json([
                'success' => true,
                'message' => 'Event created on Church Metrics!',
                'event' => $res
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create event on Church Metrics.'
        ], 500);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $this->service->updateEventOnAPI($id, $request->name);

        return response()->json([
            'success' => true,
            'message' => 'Event updated successfully!',
            'event' => [
                'id' => $id,
                'name' => $request->name,
            ]
        ]);
    }



    public function destroy($cm_id)
    {
        list($data, $apiEvents) = $this->service->request('DELETE','events/'.$cm_id.'.json', [], true);

        Cache::forget('church_events');

        return response()->json([
             'success' => true,
             'message' => 'Event deletd on Church Metrics!',
             'event' => $data
        ]);
    }
}
