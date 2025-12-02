<?php

namespace App\Http\Controllers\Location\Churchmatrix;

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

        $cm_id = $this->service->createEventToAPI($request->name);

        $event = ChurchEvent::create([
            'name' => $request->name,
            'cm_id' => $cm_id
        ]);

        return redirect()->back()->with('success', 'Event created successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $event = ChurchEvent::findOrFail($id);

        $event->update([
            'name' => $request->name
        ]);

        if ($event->cm_id) {
            $this->service->updateEventOnAPI($event->cm_id, $request->name);
        }

        return redirect()->back()->with('success', 'Event updated successfully!');
    }

    public function destroy($id)
    {
        $event = ChurchEvent::findOrFail($id);

        if ($event->cm_id) {
            $this->service->deleteEventOnAPI($event->cm_id);
        }

        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully!');
    }
}
