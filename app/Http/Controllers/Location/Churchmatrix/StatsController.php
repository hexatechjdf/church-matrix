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
        $range = parseDateRange($request->weekly_date, 'weekly');
        $user = loginUser();
        $year   = $request->year ?? now()->year;
        $months = $request->months;
        $category_id = $request->category_id ?? null;
        $time_id = $request->time_id ?? null;
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
        ->when($request->type === 'pie' && $range, function ($q) use ($range) {
            $q->whereBetween(
                DB::raw("DATE(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))"),
                [$range['from'], $range['to']]
            );
        })
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

        ->when($time_id, function ($q) use ($time_id) {
            $q->where('service_unique_time_id', $time_id);
        })
        ->when($category_id, function ($q) use ($category_id) {
            $q->where('category_unique_id', $category_id);
        })
        ->groupBy($groupY, 'month')
        ->orderBy('month')
        ->orderBy($groupY)
        ->get();

        $labels = [];
        $series = [];


        if ($request->chart == 'pie') {

            $grouped = collect($records)
                ->groupBy($column_y)
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

            $attendanceData = array_fill(0, 12, 0);

            foreach ($records as $record) {
                if ($record->{$column_y} == $id) {
                    $monthIndex = ((int) $record->{$column_x}) - 1;
                    $attendanceData[$monthIndex] = (float) $record->total_value;
                }
            }

            $datasets[] = [
                'name' => $id,
                'data' => $attendanceData,
                'backgroundColor' => 'rgba(' . rand(0,255) . ',' . rand(0,255) . ',' . rand(0,255) . ',0.5)',
                'borderColor' => 'rgba(0,0,0,0.1)',
                'borderWidth' => 1
            ];
        }

        return response()->json([
            'categories'      => $categories,
            'series'          => $datasets,
        ]);
    }


    // public function timesChartData(Request $request)
    // {
    //     $range = $request->weekly_date ? parseDateRange($request->weekly_date) : null;
    //     $user = loginUser();
    //     $year   = $request->year ?? now()->year;
    //     $months = $request->months;
    //     $category_id = $request->category_id ?? null;
    //     $time_id = $request->time_id ?? null;
    //     $campus_id = $this->churchService->getUserCampusId($request,$user);

    //     $column_y = $request->coly ?? 'service_time';
    //     $column_x = $request->colx ?? 'month';

    //     if ($column_y === 'service_time') {
    //         $selectY = "DATE_FORMAT(
    //             MIN(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ')),
    //             '%W %h:%i %p'
    //         ) as service_time";

    //         $groupY = 'service_time';
    //     } else {
    //         $selectY = "category_name";
    //         $groupY  = 'category_name';
    //     }

    //     $monthNumbers = $months ?: range(1, 12);

    //     $records = DB::table('church_records')
    //         ->selectRaw("
    //             MONTH(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ')) as month,
    //             {$selectY},
    //             SUM(value) as total_value
    //         ")
    //         ->whereYear(
    //             DB::raw("STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ')"),
    //             $year
    //         )
    //         ->when($months, function ($q) use ($months) {
    //             $q->whereIn(
    //                 DB::raw("MONTH(STR_TO_DATE(service_date_time, '%Y-%m-%dT%H:%i:%s.%fZ'))"),
    //                 $months
    //             );
    //         })
    //         ->when($campus_id, function ($q) use ($campus_id) {
    //             $q->where('campus_unique_id',$campus_id);
    //         })
    //         ->when($time_id, function ($q) use ($time_id) {
    //             $q->where('service_unique_time_id',$time_id);
    //         })
    //         ->when($category_id, function ($q) use ($category_id) {
    //             $q->where('category_unique_id',$category_id);
    //         })
    //         ->groupBy($groupY, 'month')
    //         ->orderBy('month')
    //         ->orderBy($groupY)
    //         ->get();

    //     $labels = [];
    //     $series = [];


    //     if ($request->chart == 'pie') {

    //         $grouped = collect($records)
    //             ->groupBy($column_y)
    //             ->map(function ($rows) {
    //                 return $rows->sum('total_value');
    //             });

    //         $labels = $grouped->keys()->values();
    //         $series = $grouped->values()->map(fn($v) => (int) $v);

    //         return response()->json([
    //             'labels' => $labels,
    //             'series' => $series
    //         ]);
    //     }


    //     $categories = collect($monthNumbers)
    //         ->map(fn($m) => Carbon::create()->month($m)->format('M'))
    //         ->values();

    //     $chartLabels = $records->pluck($column_x)->unique()->filter()->values();
    //     $times = $records->pluck($column_y)->unique()->filter()->values();

    //     $datasets = [];

    //     foreach ($times as $id) {

    //         $attendanceData = array_fill(0, 12, 0);

    //         foreach ($records as $record) {
    //             if ($record->{$column_y} == $id) {
    //                 $monthIndex = ((int) $record->{$column_x}) - 1;
    //                 $attendanceData[$monthIndex] = (float) $record->total_value;
    //             }
    //         }

    //         $datasets[] = [
    //             'name' => $id,
    //             'data' => $attendanceData,
    //             'backgroundColor' => 'rgba(' . rand(0,255) . ',' . rand(0,255) . ',' . rand(0,255) . ',0.5)',
    //             'borderColor' => 'rgba(0,0,0,0.1)',
    //             'borderWidth' => 1
    //         ];
    //     }

    //     return response()->json([
    //         'categories'      => $categories,
    //         'series'          => $datasets,
    //     ]);
    // }

    public function getWeekStats(Request $request)
    {
        $user = loginUser();
        $range = parseDateRange($request->daterange);

        $from = $range['from'];
        $to   = $range['to'];
        $time_id = $request->time_id ?? null;

        $column_y = $request->coly ?? 'category_name';
        $campus_id = $this->churchService->getUserCampusId($request,$user);

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

        ->when($time_id, function ($q) use ($time_id) {
             $q->where('service_unique_time_id',$time_id);
         })
        ->groupBy(DB::raw('YEARWEEK(STR_TO_DATE(service_date_time, "%Y-%m-%dT%H:%i:%s.%fZ"), 1)'))
        ->orderBy('week_no')
        ->get();

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

            ->when($time_id, function ($q) use ($time_id) {
                $q->where('service_unique_time_id',$time_id);
            })
            ->groupBy('category_name', DB::raw('YEARWEEK(STR_TO_DATE(service_date_time, "%Y-%m-%dT%H:%i:%s.%fZ"), 1)'))
            ->get();

        $categories = $weeks->map(function($w) {
            return date('d M', strtotime($w->week_start)) . ' - ' . date('d M', strtotime($w->week_end));
        });

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

        return response()->json([
            'categories' => $categories,
            'series' => $series
        ]);
    }

}
