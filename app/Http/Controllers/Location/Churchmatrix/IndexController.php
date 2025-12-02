<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTimeZone;
use DateTime;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $user = loginUser();

        $timezones = [];
        foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $tz) {
            $dt = new DateTime('now', new DateTimeZone($tz));
            $offset = $dt->getOffset();
            $hours = intdiv($offset, 3600);
            $minutes = abs(($offset % 3600) / 60);
            $sign = ($offset >= 0) ? '+' : '-';
            $timezones[$tz] = sprintf('(GMT%s%02d:%02d) %s', $sign, abs($hours), $minutes, $tz);
        }

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
