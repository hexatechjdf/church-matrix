<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTimeZone;
use DateTime;

class TimeZoneController extends Controller
{
    public function showTimezoneForm()
    {
        $user = Auth::user();

        $timezones = [];
        foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $tz) {
            $dt = new DateTime('now', new DateTimeZone($tz));
            $offset = $dt->getOffset();
            $hours = intdiv($offset, 3600);
            $minutes = abs(($offset % 3600) / 60);
            $sign = ($offset >= 0) ? '+' : '-';
            $timezones[$tz] = sprintf('(GMT%s%02d:%02d) %s', $sign, abs($hours), $minutes, $tz);
        }

        return view('time_zone.timezone', compact('user', 'timezones'));
    }

    public function saveTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string'
        ]);

        $user = Auth::user();
        $user->timezone = $request->timezone;
        $user->save();

        return redirect()->back()->with('success', 'Timezone updated successfully!');
    }
}
