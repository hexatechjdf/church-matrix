<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ChurchService;

class StatsController extends Controller
{
    protected $churchService;

    public function __construct(ChurchService $churchService)
    {
        $this->churchService = $churchService;
    }

    public function index()
    {
        $user = loginUser();
        return view('locations.churchmatrix.records.stats',compact('user'));
    }

    public function timesChartData(Request $request)
    {
        // dd($request->all());
        $user = loginUser();
        $year   = $request->year ?? now()->year;
        $months = $request->months;
        $category_id = $request->category_id ?? null;
        $campus_id = $this->churchService->getUserCampusId($request,$user);

        $column_y = $request->coly ?? 'service_time';
        $column_x = $request->colx ?? 'month';

        if ($column_y === 'service_time') {
            $selectY = "DATE_FORMAT(
                MIN(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ')),
                '%W %h:%i %p'
            ) as service_time";

            $groupY = 'service_time';
        } else {
            // category_name case
            $selectY = "category_name";
            $groupY  = 'category_name';
        }

        $monthNumbers = $months ?: range(1, 12);

        $records = DB::table('church_records')
            ->selectRaw("
                MONTH(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ')) as month,
                {$selectY},
                SUM(value) as total_value
            ")
            ->whereYear(
                DB::raw("STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ')"),
                $year
            )
            ->when($months, function ($q) use ($months) {
                $q->whereIn(
                    DB::raw("MONTH(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))"),
                    $months
                );
            })
            ->when($campus_id, function ($q) use ($campus_id) {
                $q->where('campus_unique_id',$campus_id);
            })
            ->when($category_id, function ($q) use ($category_id) {
                $q->where('category_unique_id',$category_id);
            })
            ->groupBy($groupY, 'month')
            ->orderBy('month')
            ->orderBy($groupY)
            ->get();
        $labels = [];
        $series = [];


        if ($request->chart == 'pie') {

            $grouped = collect($records)
                ->groupBy($column_y) // category_name
                ->map(function ($rows) {
                    return $rows->sum('total_value');
                });

            $labels = $grouped->keys()->values();
            $series = $grouped->values()->map(fn($v) => (int) $v);

            return response()->json([
                'labels' => $labels,
                'series' => $series
            ]);
        }


        $categories = collect($monthNumbers)
            ->map(fn($m) => Carbon::create()->month($m)->format('M'))
            ->values();

        $chartLabels = $records->pluck($column_x)->unique()->filter()->values();
        $times = $records->pluck($column_y)->unique()->filter()->values();
        $datasets = [];
        foreach ($times as $id) {
            $attendanceData = $chartLabels->map(function ($week) use ($records, $id, $column_x,$column_y) {
                $record = $records->filter(function ($item) use ($week, $id, $column_x,$column_y) {
                    return $item->{$column_x} == $week && $item->{$column_y} == $id;
                })->pluck('total_value')->toArray()[0] ?? 0;

                return $record;
            });

            if (count($attendanceData) > 0) {
                $datasets[] = [
                    // 'label' => $id,
                    'name' => $id,
                    'data' => $attendanceData,
                    'backgroundColor' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',0.5)',
                    'borderColor' => 'rgba(0,0,0,0.1)',
                    'borderWidth' => 1
                ];
            }
        }

        return response()->json([
            'categories'      => $categories,
            'series'          => $datasets,
        ]);
    }

    public function getWeekStats(Request $request)
    {
        $user = loginUser();
        $range = parseDateRange($request->daterange);

        $from = $range['from'];
        $to   = $range['to'];



        $column_y = $request->coly ?? 'category_name';
        $campus_id = $this->churchService->getUserCampusId($request,$user);

        // Step 1: Get all weeks with correct start and end dates
    $weeks = DB::table('church_records')
        ->selectRaw("
            YEARWEEK(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'), 1) as week_no,
            MIN(DATE_SUB(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'),
                INTERVAL (WEEKDAY(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))) DAY
            )) as week_start,
            MAX(DATE_ADD(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'),
                INTERVAL (6 - WEEKDAY(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))) DAY
            )) as week_end
        ")
        ->whereBetween(
            DB::raw("DATE(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))"),
            [$from, $to]
        )
        ->when($campus_id, function ($q) use ($campus_id) {
                $q->where('campus_unique_id',$campus_id);
        })
        ->groupBy(DB::raw('YEARWEEK(STR_TO_DATE(service_date_time, "%Y-%m-%dT%H:%i:%s.%fZ"), 1)'))
        ->orderBy('week_no')
        ->get();

        // dd($weeks,$from,$to);


        // Step 2: Get totals per category per week
        $records = DB::table('church_records')
            ->selectRaw("
                YEARWEEK(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'), 1) as week_no,
                category_name,
                SUM(value) as total_value
            ")
            ->whereBetween(
                DB::raw("DATE(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))"),
                [$from, $to]
            )
            ->groupBy('category_name', DB::raw('YEARWEEK(STR_TO_DATE(service_date_time, "%Y-%m-%dT%H:%i:%s.%fZ"), 1)'))
            ->get();

        // dd($records,$from,$to);


        // Step 3: Format week labels like '01 Jan - 07 Jan'
        $categories = $weeks->map(function($w) {
            return date('d M', strtotime($w->week_start)) . ' - ' . date('d M', strtotime($w->week_end));
        });

        // Step 4: Prepare series
        $series = [];
        foreach ($records->groupBy($column_y) as $name => $rows) {
            $data = $weeks->map(function($w) use ($rows) {
                $match = $rows->first(fn($r) => $r->week_no == $w->week_no);
                return $match ? (int)$match->total_value : 0;
            });
            $series[] = [
                'name' => $name,
                'data' => $data
            ];
        }

        // dd($series,$categories);

        // Step 5: Return JSON
        return response()->json([
            'categories' => $categories,
            'series' => $series
        ]);
    }







    //  $records = DB::table('church_records')
//             ->selectRaw("
//                 MONTH(dt) as month,
//                 DATE_FORMAT(dt, '%W %h:%i %p') as service_time,
//                 SUM(value) as total_value
//             ")
//             ->fromSub(function ($q) {
//                 $q->from('church_records')
//                 ->selectRaw("
//                     STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ') as dt,
//                     value
//                 ");
//             }, 't')
//             ->whereYear('dt', $year)
//             ->when($months, function ($q) use ($months) {
//                 $q->whereIn(DB::raw('MONTH(dt)'), $months);
//             })
//             ->groupBy('dt')
//             ->orderBy('month')
//             ->orderBy('dt')
//             ->get();
        // dd($records);

}
