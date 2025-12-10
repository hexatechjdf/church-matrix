<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use App\Models\ServiceTime;
use App\Services\ChurchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\churchmatrix\ServiceTimeJob;

class ServiceTimeController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return view('locations.churchmatrix.service_times.index');
    }

    public function getTimes(Request $request)
    {
        $user = loginUser();
        if (!$user->church_admin) {
            $query = ServiceTime::get();

            return response()->json([
                "data" => $query,
            ]);
        }

        $cacheKey = "service_time_temp_{$user->id}";

        $all = cache()->get($cacheKey);

        if (empty($all)) {
            dispatch_sync(new ServiceTimeJob(1, true));

            $all = cache()->get($cacheKey);
        }

        if (empty($all)) {
            return response()->json([
                "data" => [],
            ]);
        }

        $unique = collect($all)->unique('id')->values();

        return response()->json([
            "data" => $unique,
        ]);
    }

    public function getForm(Request $request)
    {
        $mode = $request->mode;
        $id = $request->id;
        $payload = $request->payload;
        $serviceTime = null;

        $campuses = $this->service->fetchCampuses();

        $events = $this->service->fetchEvents();

        if ($mode === 'edit' && $request->id) {
            $serviceTime = ServiceTime::find($request->id);
        }


        return view('locations.churchmatrix.service_times.form', compact('serviceTime', 'events', 'mode','campuses','payload'))->render();
    }

    public function manage(Request $request)
    {
        $data = $request->all();
        $e = @$data['event_id'];

        $body = [
            'day_of_week'        => $data['day_of_week'],
            'time_of_day'        => date('Y-m-d\TH:i:s\Z', strtotime($data['time_of_day'])),
            'date_start'         => $e ?  @$data['date_start'] : null,
            'date_end'           => $e ? @$data['date_end'] : null,
            'event_id'           => @$data['event_id'] ?? null,
            'campus_id'          => @$data['campus_id'],
            'replaces'           => true,
            'timezone'           => getUserTimeZone(),
        ];

        $url = $request->service_time_id ? 'service_times/'.$request->service_time_id.'.json' : 'service_times.json';
        $method = $request->id ? 'PUT' : 'POST';

        list($data, $apiEvents) = $this->service->request($method, $url, $body, true);

        if ($data === false) {
            return response()->json([
                'success' => false,
                'message' => 'API error occurred while saving service time.'
            ]);
        }
        $user = loginUser();
        \Log::info($user);
        if (!$user->church_admin) {
            if(@$data['id'])
            {
                $body['campus_name'] = @$data['campus']['slug'];
                $body['event_name'] = @$data['event']['name'];
            }
            $id = @$data['id'] ?? $request->service_time_id ?? null;
            \Log::info($id);
            if($id)
            {
                $d = ServiceTime::updateOrCreate(['cm_id' => $id],$body);
                \Log::info($d);
            }
        }else{
            $cacheKey = "service_time_temp_{$user->id}";
            Cache::forget($cacheKey);
        }


        return response()->json([
            'success' => true,
            'message' => 'Service time created successfully!'
        ]);
    }

    public function destroy($cm_id)
    {
        list($data, $apiEvents) = $this->service->request('DELETE','service_times/'.$cm_id.'.json', [], true);
        $user = loginUser();
        Cache::forget("service_time_temp_{$user->id}");
        ServiceTime::where('cm_id',$cm_id)->delete();

        return response()->json([
             'success' => true,
             'message' => 'Deletd on Church Metrics!',
             'event' => $data
        ]);
    }
}
