<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ChurchEventService;
use App\Services\ServiceTimeService;
use App\Services\ChurchService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Jobs\churchmatrix\ServiceTimeJob;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ServiceTime;

class SettingIntergration extends Controller
{
    protected $churchService;

    public function __construct(ChurchService $churchService)
    {
        $this->churchService = $churchService;
    }

   public function getEvents(Request $request)
    {
        $events = collect($this->churchService->fetchEvents()); // <-- convert to collection

        $search = $request->get('search', '');
        if ($search) {
            $events = $events->filter(function($event) use ($search) {
                return stripos($event['name'], $search) !== false; // array key access
            });
        }

        $page = (int) $request->get('page', 1);
        $perPage = 10;

        $total = $events->count();
        $eventsPage = $events->slice(($page - 1) * $perPage, $perPage)->values();

        $more = ($page * $perPage) < $total;

        return response()->json([
            'data' => $eventsPage,
            'more' => $more
        ]);
    }

    public function getCampuses(Request $request)
    {
        $events = collect($this->churchService->fetchCampuses());

        $search = $request->get('search', '');
        if ($search) {
            $events = $events->filter(function($event) use ($search) {
                return stripos($event['slug'], $search) !== false; // array key access
            });
        }

        $page = (int) $request->get('page', 1);
        $perPage = 10;

        $total = $events->count();
        $eventsPage = $events->slice(($page - 1) * $perPage, $perPage)->values();

        $more = ($page * $perPage) < $total;

        return response()->json([
            'data' => $eventsPage,
            'more' => $more
        ]);
    }

    public function getCategories(Request $request)
    {
        $events = collect($this->churchService->fetchCategories());

        dd($events);

        $search = $request->get('search', '');
        if ($search) {
            $events = $events->filter(function($event) use ($search) {
                return stripos($event['slug'], $search) !== false; // array key access
            });
        }

        $page = (int) $request->get('page', 1);
        $perPage = 10;

        $total = $events->count();
        $eventsPage = $events->slice(($page - 1) * $perPage, $perPage)->values();

        $more = ($page * $perPage) < $total;

        return response()->json([
            'data' => $eventsPage,
            'more' => $more
        ]);
    }

    public function getServiceTimes(Request $request)
    {

        $user = loginUser();

        $campus_id = null;
        try{
            $campus_id = $user->church_admin ?  $request->campus_id : @getCampusSession()['campus_id'];
        }catch(\Exception $e){
        }

        if(!$campus_id)
        {
            return response()->json([
                "data" => [],
            ]);
        }

        if (!$user->church_admin) {
            $records = ServiceTime::when($campus_id, function ($q) use ($campus_id) {
                $q->where('campus_id', $campus_id);
            })->orderBy('id', 'DESC');

            return DataTables::of($records)
                ->make(true);
        }


        $cacheKey = "service_time_temp_{$user->id}";
        $all = cache()->get($cacheKey);

        if (empty($all)) {
            dispatch_sync(new ServiceTimeJob($user->id, false)); // fill cache
            $all = cache()->get($cacheKey);
        }

        if (empty($all)) {
            return response()->json([
                "data" => [],
            ]);
        }

        $filtered = collect($all)->filter(function($item) use ($campus_id) {
            return isset($item['campus_id']) && $item['campus_id'] == $campus_id;
        })->values();

        $unique = $filtered->unique('cm_id')->values();

        return response()->json([
            "data" => $unique,
        ]);
    }


    public function index()
    {
        // $events = $this->eventService->fetchEvents();
        // $serviceTimes = $this->serviceTimeService->fetchAll();
        $campuses = [];
        $user = loginUser();
        if($user->church_admin)
        {
            $page     = $request->page ?? 1;
            $perPage  = 100;
            $t = getChurchToken('location',$user->id);
            $cacheKey = "campuses_{$user->id}_page_{$page}".'sssssss';
            $campuses = Cache::remember($cacheKey, 600, function () use ( $page, $perPage,$t) {
                $params = [
                    'page'      => $page,
                    'per_page'  => $perPage,
                ];
                $url = "campuses.json";
                list($data, $linkHeader) = $this->service->request('GET', $url, $params, true,$t);
                $l = @$linkHeader[0] ?? null;
                $pages = \parseLinks($l);

                return collect($data)->map(function ($event) use ($pages) {
                    return [
                        'id'         => $event['id'],
                        'name'       => $event['slug'],
                        'created_at' => now(),
                    ];
                })->toArray();
            });
        }else{
            $c = $suer->campus;
            $campuses['id'] = $c->campus_unique_id;
            $campuses['name'] = $c->name;
        }


        return view('locations.churchmatrix.setting-integration.index', compact('campuses'));
    }
}
