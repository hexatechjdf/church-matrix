<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        return view('locations.churchmatrix.records.stats');
    }


    public function timesChartData(Request $request)
    {
        $year   = $request->year ?: now()->year;
        $months = $request->months ? explode(',', $request->months) : [];

        $serviceDate = "STR_TO_DATE(
            REPLACE(REPLACE(service_date_time,'T',' '),'Z',''),
            '%Y-%m-%d %H:%i:%s.%f'
        )";

        $query = DB::table('church_records')
            ->select([
                DB::raw("DATE_FORMAT($serviceDate, '%b') as month_year"),
                DB::raw("HOUR($serviceDate) as service_hour"),
                DB::raw("COUNT(value) as total")
            ])
            ->whereYear(DB::raw($serviceDate), $year);

        if (!empty($months)) {
            $query->whereIn(
                DB::raw("MONTH($serviceDate)"),
                $months
            );
        }

        $data = $query
            ->groupBy('month_year', 'service_hour')
            ->orderBy(DB::raw("MONTH($serviceDate)"))
            ->orderBy('service_hour')
            ->get();

        $chartData = [];
        $hours = [];

        foreach ($data as $row) {

            $hourLabel = date('h A', strtotime($row->service_hour . ':00'));

            $chartData[$row->month_year]['month'] = $row->month_year;
            $chartData[$row->month_year][$hourLabel] = $row->total;

            $hours[$hourLabel] = true;
        }

        return response()->json([
            'json' => array_values($chartData),
            'keys' => [
                'name'   => 'month',
                'values' => array_keys($hours)
            ]
        ]);
    }
}
