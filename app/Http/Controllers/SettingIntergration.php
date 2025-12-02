<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $serviceTimes = $this->serviceTimeService->fetchAll();


        return view('setting-integration.index', compact('events', 'serviceTimes'));
    }
}
