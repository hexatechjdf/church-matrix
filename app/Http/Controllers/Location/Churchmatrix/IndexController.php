<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ChurchService;
use App\Models\User;
use App\Models\Campus;
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

    public function getUserCampusForm(Request $request)
    {
        $users = User::get();
        $campuses = Campus::get();

        return view('locations.churchmatrix.components.mappingForm', compact('users', 'campuses'));
    }

    public function saveUserCampusAjax(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'campus_id' => 'required|exists:campuses,id|unique:users,location_id,' . $request->user_id,
        ]);

        $user = User::findOrFail($request->user_id);
        $user->location_id = $request->campus_id;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Campus assigned successfully!']);

    }


}
