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

    public function getChartData(Request $request)
    {
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
            '#667eea', '#f093fb', '#f5576c', '#4ecdc4', '#45b7d1',
            '#96ceb4', '#f9ca24', '#f3722c', '#43aa8b', '#277da1'
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
                'backgroundColor' => $colors[$i % count($colors)] . '33',
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
}
