<?php

namespace App\Http\Controllers\Location\Churchmatrix;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\ChurchEventService;
use App\Services\ServiceTimeService;

class SettingIntergration extends Controller
{
    protected $eventService;
    protected $serviceTimeService;

    public function __construct(
        ChurchEventService $eventService,
        ServiceTimeService $serviceTimeService
    ) {
        $this->eventService = $eventService;
        $this->serviceTimeService = $serviceTimeService;
    }

    public function index()
    {
        $events = $this->eventService->fetchEvents();
        $serviceTimes = $this->serviceTimeService->fetchAll();
        $campuses = Http::withHeaders([
            'X-Auth-User' => 'radiwa6602@dwakm.com',
            'X-Auth-Key'  => '2b98fda4b8c22b26d7da69d816bf3ae7',
            'Accept'      => 'application/json',
        ])->get('https://churchmetrics.com/api/v1/campuses.json')->json();

        return view('locations.churchmatrix.setting-integration.index', compact('events', 'serviceTimes', 'campuses'));
    }
}
