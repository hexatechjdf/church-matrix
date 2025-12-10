<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ChurchService;
use App\Jobs\churchmatrix\ServiceTimeJob;
use Carbon\Carbon;
use App\Models\ServiceTime;
use App\Jobs\churchmatrix\SendCategoryValueToApi;

class RecordController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // $campuses   = $this->service->fetchCampuses();
        // $events     = $this->service->fetchEvents();
        // $categories = $this->service->fetchCategories();

        if ($request->ajax()) {
            $records = DB::table('church_records')->orderBy('id', 'DESC');

            return DataTables::of($records)
                ->editColumn('service_date_time', fn($r) => Carbon::parse($r->service_date_time)->format('d M Y'))
                ->make(true);
            // Actions column ab blade mein banega → controller clean rahega
        }

        return view('locations.churchmatrix.records.index');
    }

    public function getForm(Request $request)
    {
        $categories = $this->service->fetchCategories();
        $campuses   = $this->service->fetchCampuses();
        $events     = $this->service->fetchEvents();

        $mode = $request->mode;
        $id = $request->id;
        $payload = $request->payload;
        $serviceTime = null;


        if ($mode === 'edit' && $request->id) {
            $serviceTime = ServiceTime::find($request->id);
        }

        $view =  view('locations.churchmatrix.records.form', get_defined_vars())->render();

        return response()->json(['html' => $view]);
    }

    public function getTimesPaginated(Request $request)
    {
        $user = loginUser();
        $search = $request->get('search', '');
        $page   = $request->get('page', 1);
        $limit  = 100;

        // 1. Church Admin → DB pagination (BEST)
        if ($user->church_admin) {

            $query = ServiceTime::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('time_of_day', 'LIKE', "%$search%")
                    ->orWhere('time_of_day', 'LIKE', "%$search%");
                });
            }

            $results = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                "data" => $results->items(),
                "more" => $results->hasMorePages()
            ]);
        }


        // 2. Non-admin → cached collection
        $cacheKey = "service_time_temp_{$user->id}";
        $all = cache()->get($cacheKey);

        if (empty($all)) {
            dispatch_sync(new ServiceTimeJob($user->id, false));
            $all = cache()->get($cacheKey);
        }

        if (empty($all)) {
            return response()->json(["data" => [], "more" => false]);
        }

        $all = collect($all);

        // Apply search on collection
        if ($search) {
            $all = $all->filter(function ($st) use ($search) {
                return str_contains(strtolower($st['time_of_day'] ?? ''), strtolower($search))
                    || str_contains(strtolower($st['time_of_day'] ?? ''), strtolower($search));
            });
        }

        $total = $all->count();
        $data = $all->slice(($page - 1) * $limit, $limit)->values();

        return response()->json([
            "data" => $data,
            "more" => ($page * $limit) < $total
        ]);
    }

    public function manage(Request $request)
    {
        dd($request->all());
        $user = loginUser();
        $id              = $request->record_id;
        $event_id        = $request->event_id;
        $campus_id       = $request->campus_id;
        $service_time_id = $request->service_time_id;
        $serviceTimezone = "Central Time (US & Canada)";
        $serviceDateTime = now()->toISOString();

        $finalPayload = [];

        foreach ($request->category_values as $categoryId => $value) {

            if (empty($value)) {
                continue;
            }

            $data = [
                "category_id"       => (int) $categoryId,
                "campus_id"         => (int) $campus_id,
                "service_time_id"   => (int) $service_time_id,
                "value"             => (int) $value,
                "replaces"          => true,
                "event_id"          => (int) $event_id,
            ];

            dispatch_sync((new SendCategoryValueToApi($data,$user->id,$id)));

            $finalPayload[] = $data;
        }

        return 1;

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


}
