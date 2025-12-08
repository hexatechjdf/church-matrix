<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ChurchEventService;
use App\Services\ServiceTimeService;
use App\Services\ChurchService;
use Illuminate\Support\Facades\Cache;

class SettingIntergration extends Controller
{
    protected $eventService;
    protected $serviceTimeService;
    protected $service;

    public function __construct(
        ChurchEventService $eventService,
        ChurchService $service,
        ServiceTimeService $serviceTimeService
    ) {
        $this->eventService = $eventService;
        $this->service = $service;
        $this->serviceTimeService = $serviceTimeService;
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
