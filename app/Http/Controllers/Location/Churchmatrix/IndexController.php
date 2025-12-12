<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChurchService;
use DateTimeZone;
use DateTime;

class IndexController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = loginUser();
        $timezones = getTimeZones();

        $settings = getChurchToken('location');

        $regions =  $settings && $user->church_admin ? $this->service->fetchRegions($settings) : null;

        return view('locations.churchmatrix.index',get_defined_vars());
    }

    public function setCampus(Request $request)
    {
       $name = $request->name ?? 'testing';
       $description = $name;
       $user = loginUser();
       $region_id = get_setting($user->id, 'region');
       $timezone = $user->timezone ?? 'London';
    }


}
