<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use App\Models\ServiceTime;
use App\Services\ChurchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\churchmatrix\ServiceTimeJob;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ServiceTimeController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = loginUser();
        return view('locations.churchmatrix.service_times.index',get_defined_vars());
    }

    public function getTimes(Request $request)
    {
        $user = loginUser();
        // || $user->role == 0
        if (!$user->church_admin ) {
            $records = ServiceTime::when(!$user->church_admin,function($q)use($user){
                $campus = @$user->campus;
                $q->where('campus_id', $campus->campus_unique_id);
            })->orderBy('id', 'DESC');

            return DataTables::of($records)
                ->make(true);
        }

        $cacheKey = "service_time_temp_{$user->id}";
        $all = cache()->get($cacheKey);

        if (empty($all)) {
            dispatch_sync(new ServiceTimeJob($user->id, false));

            $all = cache()->get($cacheKey);
        }

        if (empty($all)) {
            return response()->json([
                "data" => [],
            ]);
        }

        $unique = collect($all)->unique('cm_id')->values();

        return response()->json([
            "data" => $unique,
        ]);
    }

    public function getForm(Request $request)
    {
        $payload = $request->payload;
        $id = @$payload['cm_id'];

        $serviceTime = null;
        $user = loginUser();

        if ($id) {
            $serviceTime = ServiceTime::where('cm_id',$request->id);
        }


        return view('locations.churchmatrix.service_times.form', compact('serviceTime','payload','user','id'))->render();
    }

    public function manage(Request $request)
    {
        $request->validate([
            'service_time_id' => 'nullable',
            'day_of_week'     => 'required',
            'time_of_day'     => 'required',
            'event_id'        => 'nullable',
            'date_start' => 'required_with:event_id|nullable|date',
            'date_end'   => 'required_with:event_id|nullable|date|after_or_equal:date_start',
        ]);

        $data = $request->all();
        $e = @$data['event_id'];

        $user = loginUser();
        $campus_id = null;
        try{
            $campus_id = $user->church_admin ?  $request->campus_id : @getCampusSession()['campus_id'];
        }catch(\Exception $e){
        }

        if(!$campus_id)
        {
            return response()->json([
                'success' => false,
                'message' => 'API error occurred while saving service time.'
            ]);
        }

        $body = [
            'day_of_week'        => $data['day_of_week'],
            'time_of_day'        => date('Y-m-d\TH:i:s\Z', strtotime($data['time_of_day'])),
            'date_start'         => $e ?  @$data['date_start'] : null,
            'date_end'           => $e ? @$data['date_end'] : null,
            'event_id'           => @$data['event_id'] ?? null,
            'campus_id'          => $campus_id,
            'replaces'           => true,
            'timezone'           => getUserTimeZone(),
        ];

        $url = $request->service_time_id ? 'service_times/'.$request->service_time_id.'.json' : 'service_times.json';
        $method = $request->id ? 'PUT' : 'POST';

        $data = $this->service->request($method, $url, $body);

        if ($data === false) {
            return response()->json([
                'success' => false,
                'message' => 'API error occurred while saving service time.'
            ]);
        }
        $user = loginUser();
        if (!$user->church_admin ||  $user->role == 0) {
            if(@$data['id'])
            {
                $body['campus_name'] = @$data['campus']['slug'];
                $body['event_name'] = @$data['event']['name'];
            }
            $id = @$data['id'] ?? $request->service_time_id ?? null;
            if($id)
            {
                $d = ServiceTime::updateOrCreate(['cm_id' => $id],$body);
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
        $data = $this->service->request('DELETE','service_times/'.$cm_id.'.json', [], false,null,true);

        if (!empty($data['errors'])) {
            return response()->json([
                'success' => false,
                'message' => 'Server error!',
            ]);
        }

        $user = loginUser();
        Cache::forget("service_time_temp_{$user->id}");
        ServiceTime::where('cm_id',$cm_id)->delete();

        return response()->json([
             'success' => true,
             'message' => 'Deletd on Church Metrics!',
        ]);
    }
}
