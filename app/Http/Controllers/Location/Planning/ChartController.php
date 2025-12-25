<?php

namespace App\Http\Controllers\Location\Planning;

use Illuminate\Http\Request;
use App\Models\CrmToken;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\PlanningService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ChartController extends Controller
{
    private $planningService;

    public function __construct(PlanningService $planningService)
    {
        $this->planningService = $planningService;
    }

    public function index()
    {
        return view('locations.planning.event_filter');
    }

public function getChartJson(Request $request)
{
    $year = $request->year ?? date('Y');
    $months = $request->months ?? [];
    $eventId = $request->event_id;
    $attendanceId = $request->attendance_id;
    $startDate = $request->start_date;
    $endDate = $request->end_date;

    $cacheKey = "chart_json_{$year}_" . implode(',', $months) . "_{$eventId}_{$attendanceId}_{$startDate}_{$endDate}";
    $data = Cache::remember($cacheKey, 300, function () use ($year, $months, $eventId, $attendanceId, $startDate, $endDate) {

        $query = DB::table('events_data')
            ->where('value', '>', 0)
            ->whereNotNull('service_name');

        if ($startDate && $endDate) {
            $query->whereBetween('service_date', [$startDate, $endDate]);


            $query->selectRaw("
                FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) + 1 as week_number,
                CONCAT('Week ', FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) + 1) as week_label,
                DATE_FORMAT(DATE_ADD('{$startDate}', INTERVAL FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) * 7 DAY), '%b %d') as week_start,
                DATE_FORMAT(DATE_ADD('{$startDate}', INTERVAL (FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) * 7) + 6 DAY), '%b %d') as week_end,
                CONCAT('Week ', FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) + 1,
                       ' (', DATE_FORMAT(DATE_ADD('{$startDate}', INTERVAL FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) * 7 DAY), '%b %d'),
                       ' - ', DATE_FORMAT(DATE_ADD('{$startDate}', INTERVAL (FLOOR(DATEDIFF(service_date, '{$startDate}') / 7) * 7) + 6 DAY), '%b %d'), ')') as label,
                service_name,
                SUM(value) as total
            ")
            ->groupBy(DB::raw("FLOOR(DATEDIFF(service_date, '{$startDate}') / 7)"), 'service_name')
            ->orderBy('week_number');

        } elseif (!empty($months)) {
            $query->whereYear('service_date', $year)
                ->whereIn(DB::raw('MONTH(service_date)'), array_map('intval', $months));
            $query->selectRaw("DATE_FORMAT(service_date,'%b') as label")
                ->selectRaw("service_name")
                ->selectRaw("SUM(value) as total")
                ->groupBy(DB::raw("MONTH(service_date)"), 'service_name')
                ->orderBy(DB::raw("MONTH(service_date)"));
        } else {
            $query->whereYear('service_date', $year)
                ->selectRaw("DATE_FORMAT(service_date,'%b') as label")
                ->selectRaw("service_name")
                ->selectRaw("SUM(value) as total")
                ->groupBy(DB::raw("MONTH(service_date)"), 'service_name')
                ->orderBy(DB::raw("MONTH(service_date)"));
        }

        if ($eventId) $query->where('event_id', $eventId);
        if ($attendanceId) $query->where('attendance_id', $attendanceId);

        $rows = $query->get();

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $totalWeeks = ceil($start->diffInDays($end) / 7);

            $labels = [];
            for ($week = 1; $week <= $totalWeeks; $week++) {
                $weekStartDate = $start->copy()->addDays(($week - 1) * 7);
                $weekEndDate = $weekStartDate->copy()->addDays(6);
                if ($weekEndDate > $end) $weekEndDate = $end;

                $labels[] = "Week {$week} ({$weekStartDate->format('M d')} - {$weekEndDate->format('M d')})";
            }

            $labels = collect($labels);
        } else {
            $labels = $rows->pluck('label')->unique()->values();
        }

        $events = $rows->pluck('service_name')->unique();

        $series = [];
        foreach ($events as $eventName) {
            if (empty($eventName)) continue;

            $eventData = [];

            if ($startDate && $endDate) {
                $weekData = [];
                foreach ($rows->where('service_name', $eventName) as $row) {
                    $weekData[$row->week_number] = (int)$row->total;
                }

                for ($week = 1; $week <= count($labels); $week++) {
                    $eventData[] = $weekData[$week] ?? 0;
                }
            } else {
                $eventData = $labels->map(
                    fn($l) => (int)$rows->where('label', $l)
                        ->where('service_name', $eventName)
                        ->sum('total')
                )->values();
            }

            $series[] = [
                'name' => $eventName,
                'data' => $eventData
            ];
        }

        $availableYears = DB::table('events_data')
            ->selectRaw('YEAR(service_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return [
            'categories' => $labels,
            'series' => $series,
            'available_years' => $availableYears,
            'filter_type' => $startDate && $endDate ? 'date_range' : (!empty($months) ? 'months' : 'yearly')
        ];
    });

    return response()->json($data);
}


    public function getPieChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $eventId = $request->input('event_id');

        $userId = auth()->id();
        // dd($userId);

        if (!$userId) {
            $userId = $request->user()->id ?? null;
        }

        if (!$userId) {
            return response()->json([
                'labels' => ['User not authenticated'],
                'values' => [1],
                'years' => [$year]
            ], 401);
        }

        $token = CrmToken::where('user_id', $userId)
            ->where('crm_type', 'planning')
            ->first();

        if (!$token || !$token->access_token) {
            return response()->json([
                'labels' => ['No Planning Center Token'],
                'values' => [1],
                'years' => [$year]
            ], 400);
        }

        $attendanceNames = Cache::remember('attendance_names_' . $userId, 3600, function () use ($token) {
            $response = $this->planningService->planning_api_call(
                "check-ins/v2/attendance_types?per_page=100",
                'get',
                '',
                [],
                false,
                $token->access_token
            );

            $names = [];
            foreach ($response->data ?? [] as $item) {
                $id = $item->id ?? null;
                $name = $item->attributes->name ?? null;

                if ($id && $name) {
                    $names[$id] = $name;
                }
            }

            $defaultNames = [
                'regular' => 'Regular',
                'guest' => 'Guest',
                'volunteer' => 'Volunteer'
            ];

            foreach ($defaultNames as $id => $name) {
                $names[$id] = $name;
            }

            return $names;
        });

        $query = DB::table('events_data')
            ->whereYear('service_date', $year)
            ->where('value', '>', 0)
            ->whereNotNull('attendance_id');

        if ($eventId) $query->where('event_id', $eventId);

        $results = $query
            ->select('attendance_id', DB::raw('SUM(value) as total'))
            ->groupBy('attendance_id')
            ->orderByDesc('total')
            ->get();

        if ($results->isEmpty()) {
            return response()->json([
                'labels' => ['No Data'],
                'values' => [1],
                'years' => [$year],
                'year' => (int)$year
            ]);
        }

        $labels = [];
        $values = [];

        foreach ($results as $row) {
            $labels[] = $attendanceNames[$row->attendance_id] ?? ucfirst($row->attendance_id);
            $values[] = (int)$row->total;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'years' => [$year],
            'year' => (int)$year,
            'event_id' => $eventId
        ]);
    }

    // public function getEventsChartData(Request $request)
    // {
    //     $year = $request->input('year', date('Y'));
    //     $months = $request->input('months');

    //     $data = DB::table('events_data')
    //         ->whereYear('service_date', $year)
    //         ->where('value', '>', 0)
    //         ->whereNotNull('service_name');

    //     if (!empty($months) && is_array($months)) {
    //         $data->whereIn(DB::raw('MONTH(service_date)'), array_map('intval', $months));
    //     }

    //     $rows = $data->select(
    //         'service_name as event',
    //         DB::raw('MONTH(service_date) as month'),
    //         DB::raw('SUM(value) as total')
    //     )
    //         ->groupBy('event', 'month')
    //         ->orderBy('month')
    //         ->get();

    //     $events = $rows->pluck('event')->unique()->sort()->values();
    //     $series = [];

    //     foreach ($events as $event) {
    //         $monthlyData = array_fill(0, 12, 0);
    //         foreach ($rows->where('event', $event) as $row) {
    //             $monthlyData[$row->month - 1] = (int)$row->total;
    //         }
    //         $series[] = ['name' => $event, 'data' => $monthlyData];
    //     }

    //     $availableYears = DB::table('events_data')
    //         ->whereNotNull('service_date')
    //         ->selectRaw('YEAR(service_date) as year')
    //         ->distinct()
    //         ->orderByDesc('year')
    //         ->pluck('year')
    //         ->toArray();

    //     return response()->json([
    //         'series' => $series,
    //         'available_years' => $availableYears,
    //         'year' => (int)$year
    //     ]);
    // }
}
