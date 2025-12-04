<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use App\Models\ChurchEvent;
use App\Services\ChurchEventService;
use Illuminate\Http\Request;

class ChurchEventController extends Controller
{
    protected $service;

    public function __construct(ChurchEventService $service)
    {
        $this->service = $service;
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
        $success = $this->service->deleteEventOnAPI($cm_id);

        if ($success) {

            return response()->json([
                'success' => true,
                'message' => 'Event permanently deleted from ChurchMetrics!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete from ChurchMetrics. Try again.'
        ], 500);
    }
}
