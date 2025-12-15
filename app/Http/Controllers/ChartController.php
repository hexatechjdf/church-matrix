<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function index()
    {
        $campuses = DB::table('church_records')
            ->whereNotNull('campus_unique_id')
            ->select('campus_unique_id')
            ->distinct()
            ->get();

        $events = DB::table('church_records')
            ->whereNotNull('event_unique_id')
            ->select('event_unique_id')
            ->distinct()
            ->get();

        return view('charts', compact('campuses', 'events'));
    }


    public function eventFilter()
    {
        $events = DB::table('events_data')
            ->select('event_id', 'event_name', 'service_name')
            ->distinct()
            ->orderBy('service_date', 'asc')
            ->get();

        return view('event_filter', compact('events'));
    }




    public function getChartJSData(Request $request)
    {
        $data = DB::table('events_data');
        $type = 'day';
        $column = 'month_year';
        if ($type == 'month') {

            $data =  $data->select(
                DB::raw("DATE_FORMAT(headcount_created_at, '%m-%Y') as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('month_year', 'attendance_id')
                ->orderBy('month_year');
        } else if ($type == 'week') {
            $column = 'first_created_date';
            $data =  $data->select(
                DB::raw("week_reference as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('week_reference', 'attendance_id')
                ->orderBy('week_reference');
        } else if ($type == 'year') {

            $data =  $data->select(
                DB::raw("DATE_FORMAT(headcount_created_at, '%Y') as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('month_year', 'attendance_id')
                ->orderBy('month_year');
        } else if ($type == 'day') {

            $data =  $data->select(
                DB::raw("DATE_FORMAT(headcount_created_at, '%d-%m-%Y') as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('month_year', 'attendance_id')
                ->orderBy('month_year');
        }

        $data  = $data->orderByDesc('first_created_date')
            ->get();

        // Prepare data for Chart.js
        $chartLabels = $data->pluck($column)->unique()->filter()->slice(-8)->values();
        $attendanceIds = $data->pluck('attendance_id')->unique()->filter()->values();
        // dd($chartLabels,$attendanceIds);
        $datasets = [];
        foreach ($attendanceIds as $id) {
            $attendanceData = $chartLabels->map(function ($week) use ($data, $id, $column) {
                $record = $data->filter(function ($item) use ($week, $id, $column) {
                    return $item->{$column} == $week && $item->attendance_id == $id;
                })->pluck('attendance_count')->toArray()[0] ?? 0;

                return $record;
            });

            if (count($attendanceData) > 0) {
                $datasets[] = [
                    'label' => $id,
                    'data' => $attendanceData,
                    'backgroundColor' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',0.5)',
                    'borderColor' => 'rgba(0,0,0,0.1)',
                    'borderWidth' => 1
                ];
            }
        }

        return response()->json([
            'labels' => $chartLabels,
            'datasets' => $datasets
        ]);


        $query = DB::table('church_records');

        if ($request->filled('campus') && $request->campus !== '') {
            $query->where('campus_unique_id', $request->campus);
        }
        if ($request->filled('event') && $request->event !== '') {
            $query->where('event_unique_id', $request->event);
        }
        if ($request->filled('week_start') && $request->filled('week_end')) {
            $query->whereBetween('service_date_time', [
                $request->week_start . ' 00:00:00',
                $request->week_end . ' 23:59:59'
            ]);
        }

        $data = $query
            ->selectRaw("DATE(service_date_time) as service_date")
            ->selectRaw('campus_unique_id')
            ->selectRaw('event_unique_id')
            ->selectRaw('SUM(value) as total_value')
            ->groupBy('service_date', 'campus_unique_id', 'event_unique_id')
            ->orderBy('service_date')
            ->get();

        if (!$request->filled('campus') && !$request->filled('event')) {
            $grouped = $data->groupBy('campus_unique_id');
        } elseif ($request->filled('campus') && !$request->filled('event')) {
            $grouped = $data->groupBy('event_unique_id');
        } elseif (!$request->filled('campus') && $request->filled('event')) {
            $grouped = $data->groupBy('campus_unique_id');
        } else {
            $grouped = ['selected' => $data];
        }

        $datasets = [];
        $colors = [
            '#667eea',
            '#f093fb',
            '#f5576c',
            '#4ecdc4',
            '#45b7d1',
            '#96ceb4',
            '#f9ca24',
            '#f3722c',
            '#43aa8b',
            '#277da1'
        ];

        $i = 0;
        foreach ($grouped as $key => $rows) {
            if (!$request->filled('campus') && !$request->filled('event')) {
                $label = 'Campus ' . $key;
            } elseif ($request->filled('campus') && !$request->filled('event')) {
                $label = 'Event ' . $key;
            } elseif (!$request->filled('campus') && $request->filled('event')) {
                $label = 'Campus ' . $key;
            } else {
                $label = 'Campus ' . $request->campus . ' - Event ' . $request->event;
            }

            $datasets[] = [
                'label' => $label,
                'data' => $rows->pluck('total_value')->toArray(),
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => $colors[$i % count($colors)] . '90',
                'fill' => true,
                'tension' => 0.4,
                'pointRadius' => 5,
                'pointHoverRadius' => 8
            ];
            $i++;
        }

        $labels = $data->pluck('service_date')->unique()->values()->toArray();

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }



    // billobard chart

    public function getChartData(Request $request)
    {


        $data = DB::table('events_data');
        $type = 'day';
        $column = 'month_year';
        if ($type == 'month') {

            $data =  $data->select(
                DB::raw("DATE_FORMAT(headcount_created_at, '%m-%Y') as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('month_year', 'attendance_id')
                ->orderBy('month_year');
        } else if ($type == 'week') {
            $column = 'first_created_date';
            $data =  $data->select(
                DB::raw("week_reference as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('week_reference', 'attendance_id')
                ->orderBy('week_reference');
        } else if ($type == 'year') {

            $data =  $data->select(
                DB::raw("DATE_FORMAT(headcount_created_at, '%Y') as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('month_year', 'attendance_id')
                ->orderBy('month_year');
        } else if ($type == 'day') {

            $data =  $data->select(
                DB::raw("DATE_FORMAT(headcount_created_at, '%d-%m-%Y') as month_year"),
                'attendance_id',
                DB::raw('COUNT(*) as attendance_count'),
                DB::raw('DATE(MIN(headcount_created_at)) as first_created_date')
            )
                //->whereBetween('headcount_created_at', ['2025-01-01', '2025-12-31'])
                ->groupBy('month_year', 'attendance_id')
                ->orderBy('month_year');
        }

        $data  = $data->whereNotNull('attendance_id')->having('attendance_count', '>', 0)->orderByDesc('first_created_date')
            ->get()->map(function ($item) {
                $key = $item->attendance_id;
                $item->{$key} = $item->attendance_count;
                return $item;
            });;




        // Prepare data for Chart.js
        $chartLabels = $data->pluck($column)->unique()->filter()->slice(-8)->values();
        $attendanceIds = $data->pluck('attendance_id')->unique()->filter()->values()->map(function ($item) {
            return $item;
        });;



        // dd($chartLabels,$attendanceIds);
        // $datasets = [];
        // foreach ($attendanceIds as $id) {
        //     $attendanceData = $chartLabels->map(function ($week) use ($data, $id, $column) {
        //         $record = $data->filter(function ($item) use ($week, $id, $column) {
        //             return $item->{$column} == $week && $item->attendance_id == $id;
        //         })->pluck('attendance_count')->toArray()[0] ?? 0;

        //         return $record;
        //     });

        //     if (count($attendanceData) > 0) {
        //         $datasets[] = [
        //             'label' => $id,
        //             'data' => $attendanceData,
        //             'backgroundColor' => 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ',0.5)',
        //             'borderColor' => 'rgba(0,0,0,0.1)',
        //             'borderWidth' => 1
        //         ];
        //     }
        // }

        return response()->json([
            'keys' => ["name" => "month_year", "values" => $attendanceIds],
            'json' => $data
        ]);


        $query = DB::table('church_records');

        if ($request->filled('campus') && $request->campus !== '') {
            $query->where('campus_unique_id', $request->campus);
        }
        if ($request->filled('event') && $request->event !== '') {
            $query->where('event_unique_id', $request->event);
        }
        if ($request->filled('week_start') && $request->filled('week_end')) {
            $query->whereBetween('service_date_time', [
                $request->week_start . ' 00:00:00',
                $request->week_end . ' 23:59:59'
            ]);
        }

        $data = $query
            ->selectRaw("DATE(service_date_time) as service_date")
            ->selectRaw('campus_unique_id')
            ->selectRaw('event_unique_id')
            ->selectRaw('SUM(value) as total_value')
            ->groupBy('service_date', 'campus_unique_id', 'event_unique_id')
            ->orderBy('service_date')
            ->get();

        if (!$request->filled('campus') && !$request->filled('event')) {
            $grouped = $data->groupBy('campus_unique_id');
        } elseif ($request->filled('campus') && !$request->filled('event')) {
            $grouped = $data->groupBy('event_unique_id');
        } elseif (!$request->filled('campus') && $request->filled('event')) {
            $grouped = $data->groupBy('campus_unique_id');
        } else {
            $grouped = ['selected' => $data];
        }

        $datasets = [];
        $colors = [
            '#667eea',
            '#f093fb',
            '#f5576c',
            '#4ecdc4',
            '#45b7d1',
            '#96ceb4',
            '#f9ca24',
            '#f3722c',
            '#43aa8b',
            '#277da1'
        ];

        $i = 0;
        foreach ($grouped as $key => $rows) {
            if (!$request->filled('campus') && !$request->filled('event')) {
                $label = 'Campus ' . $key;
            } elseif ($request->filled('campus') && !$request->filled('event')) {
                $label = 'Event ' . $key;
            } elseif (!$request->filled('campus') && $request->filled('event')) {
                $label = 'Campus ' . $key;
            } else {
                $label = 'Campus ' . $request->campus . ' - Event ' . $request->event;
            }

            $datasets[] = [
                'label' => $label,
                'data' => $rows->pluck('total_value')->toArray(),
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => $colors[$i % count($colors)] . '90',
                'fill' => true,
                'tension' => 0.4,
                'pointRadius' => 5,
                'pointHoverRadius' => 8
            ];
            $i++;
        }

        $labels = $data->pluck('service_date')->unique()->values()->toArray();

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }

    // ChartController.php

    public function getApexChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $months = $request->input('months');

        $query = DB::table('events_data')
            ->whereNotNull('service_name')
            ->whereNotNull('value')
            ->whereYear('service_date', $year);

        if ($months && is_array($months)) {
            $query->whereIn(DB::raw('MONTH(service_date)'), array_map('intval', $months));
        }

        $data = $query->select(
            'service_name as event',
            DB::raw('MONTH(service_date) as month'),
            DB::raw('SUM(value) as total')
        )
            ->groupBy('event', 'month')
            ->orderBy('month')
            ->get();

        $monthNames = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ];

        $events = $data->pluck('event')->unique()->sort()->values();

        $series = [];

        foreach ($events as $event) {
            $monthlyData = array_fill(0, 12, 0); // Jan to Dec

            foreach ($data->where('event', $event) as $row) {
                $monthlyData[$row->month - 1] = (int)$row->total;
            }

            // Apply month filter if any
            if ($months && is_array($months)) {
                $filtered = [];
                foreach ($months as $m) {
                    $filtered[] = $monthlyData[intval($m) - 1] ?? 0;
                }
                $monthlyData = $filtered;
            }

            $series[] = [
                'name' => $event,
                'data' => $monthlyData
            ];
        }

        // Categories (X-axis)
        $categories = ($months && is_array($months))
            ? collect($months)->map(fn($m) => $monthNames[intval($m) - 1])->values()->toArray()
            : $monthNames;

        return response()->json([
            'series' => $series,
            'categories' => $categories,
            'year' => (int)$year,
            'available_years' => DB::table('events_data')
                ->whereNotNull('service_date')
                ->selectRaw('YEAR(service_date) as year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->toArray()
        ]);
    }
}
