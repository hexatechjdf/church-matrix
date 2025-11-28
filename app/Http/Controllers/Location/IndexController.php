<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        return view('locations.churchmatrix.index');
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
