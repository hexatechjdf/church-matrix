<?php

namespace App\Http\Controllers;

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

    public function store(StoreServiceTimeRequest $request)
    {
        $cm_id = $this->service->createServiceTimeToAPI($request->validated());

        $serviceTime = ServiceTime::create([
            'cm_id' => $cm_id,
            'campus_id' => $request->campus_id,
            'day_of_week' => $request->day_of_week,
            'time_of_day' => $request->time_of_day,
            'timezone' => $request->timezone,
            'relation_to_sunday' => $request->relation_to_sunday,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'replaces' => $request->replaces,
            'event_id' => $request->event_id,
        ]);

        return redirect()->back()->with('success', 'Service Time created successfully!');
    }

  public function update(StoreServiceTimeRequest $request, ServiceTime $serviceTime)
    {
        $serviceTime->update($request->validated());

        if ($serviceTime->cm_id) {
            $this->service->updateServiceTimeOnAPI($serviceTime->cm_id, $request->validated());
        }

        return back()->with('success', 'Service Time updated successfully!');
    }
}
