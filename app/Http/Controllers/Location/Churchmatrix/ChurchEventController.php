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
