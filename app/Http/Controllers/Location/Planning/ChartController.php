<?php

namespace App\Http\Controllers\Location\Planning;

use Illuminate\Http\Request;
use App\Models\CrmToken;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\PlanningService;



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



    public function getLineChartData(Request $request)
    {
        $type = $request->input('type', 'week');

        $query = DB::table('events_data')
            ->where('value', '>', 0)
            ->whereNotNull('service_date')
            ->whereNotNull('attendance_id');

        if ($type === 'day') {
            $query->selectRaw("DATE_FORMAT(service_date, '%d-%m-%Y') as label")
                ->selectRaw('attendance_id')
                ->selectRaw('SUM(value) as total')
                ->groupBy('service_date', 'attendance_id');
        } elseif ($type === 'week') {
            $query->selectRaw('week_reference as label')
                ->selectRaw('attendance_id')
                ->selectRaw('SUM(value) as total')
                ->groupBy('week_reference', 'attendance_id');
        } elseif ($type === 'month') {
            $query->selectRaw("DATE_FORMAT(service_date, '%m-%Y') as label")
                ->selectRaw('attendance_id')
                ->selectRaw('SUM(value) as total')
                ->groupBy(DB::raw("DATE_FORMAT(service_date, '%m-%Y')"), 'attendance_id');
        } elseif ($type === 'year') {
            $query->selectRaw('YEAR(service_date) as label')
                ->selectRaw('attendance_id')
                ->selectRaw('SUM(value) as total')
                ->groupBy(DB::raw('YEAR(service_date)'), 'attendance_id');
        } else {
            $type = 'week';
        }

        $results = $query->orderBy('label')->get();

        if ($results->isEmpty()) {
            return response()->json(['labels' => [], 'datasets' => []]);
        }

        $labels = $results->pluck('label')->unique()->sort()->values()->slice(-12);
        $types = $results->pluck('attendance_id')->unique();

        $colors = ['#4ecdc4', '#f3722c', '#45b7d1', '#f9ca24', '#f5576c', '#96ceb4', '#667eea'];

        $datasets = [];
        foreach ($types as $index => $type) {
            $data = $labels->map(
                fn($label) =>
                $results->where('label', $label)->where('attendance_id', $type)->sum('total')
            );

            if ($data->sum() > 0) {
                $color = $colors[$index % count($colors)];
                $datasets[] = [
                    'label' => $this->formatAttendanceLabel($type),
                    'data' => $data->values(),
                    'backgroundColor' => $color . '90',
                    'borderColor' => $color,
                    'fill' => true,
                    'tension' => 0.4,
                ];
            }
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets,
            'type' => $type
        ]);
    }


    public function getPieChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $eventId = $request->input('event_id'); 

        $userId = 883;
        $token = CrmToken::where('user_id', $userId)
            ->where('crm_type', 'planning')
            ->first();

        if (!$token || !$token->access_token) {
            return response()->json([
                'labels' => ['No Token'],
                'values' => [1],
                'years' => [$year]
            ]);
        }

        $userToken = $token->access_token;
        $query = DB::table('events_data')
            ->whereYear('service_date', $year)
            ->where('value', '>', 0)
            ->whereNotNull('attendance_id');

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $results = $query
            ->select('attendance_id', DB::raw('SUM(value) as total'))
            ->groupBy('attendance_id')
            ->orderByDesc('total')
            ->get();

        \Log::info("Pie Chart Request - Year: {$year}, Event ID: {$eventId}, Results: " . $results->count());

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
            $attendanceId = $row->attendance_id;

            if (in_array($attendanceId, ['regular', 'guest', 'volunteer'])) {
                $labels[] = ucfirst($attendanceId);
            } else {
                try {
                    $url = "check-ins/v2/attendance_types/{$attendanceId}";
                    $response = $this->planningService->planning_api_call($url, 'get', '', [], false, $userToken);

                    if ($response && isset($response->data->attributes->name)) {
                        $labels[] = $response->data->attributes->name;
                    } else {
                        $labels[] = "Attendance {$attendanceId}";
                    }
                } catch (\Exception $e) {
                    $labels[] = "Type {$attendanceId}";
                }
            }

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

  


    private function formatAttendanceLabel($id)
    {
        return match ($id) {
            'regular'    => 'Regulars',
            'guest'      => 'Guests',
            'volunteer'  => 'Volunteers',
            default      => "Type {$id}"
        };
    }
    public function getChartJson(Request $request)
    {
        $year = $request->year ?? date('Y');
        $months = $request->months ?? [];
        $eventId = $request->event_id;
        $attendanceId = $request->attendance_id;

        $query = DB::table('events_data')->whereYear('service_date', $year)->where('value', '>', 0);

        if ($eventId) $query->where('event_id', $eventId);
        if ($attendanceId) $query->where('attendance_id', $attendanceId);
        if (!empty($months)) $query->whereIn(DB::raw('MONTH(service_date)'), array_map('intval', $months));

        $rows = $query
            ->selectRaw("DATE_FORMAT(service_date,'%b') as label")
            ->selectRaw("service_name")
            ->selectRaw("SUM(value) as total")
            ->groupBy(DB::raw("MONTH(service_date)"), 'service_name')
            ->orderBy(DB::raw("MONTH(service_date)"))
            ->get();

        $labels = $rows->pluck('label')->unique()->values();
        $events = $rows->pluck('service_name')->unique();

        $series = [];
        foreach ($events as $eventName) {
            if (empty($eventName)) continue;

            $series[] = [
                'name' => $eventName,
                'data' => $labels->map(
                    fn($l) =>
                    (int)$rows->where('label', $l)
                        ->where('service_name', $eventName)
                        ->sum('total')
                )->values()
            ];
        }

        return response()->json([
            'categories' => $labels,
            'series' => $series,
            'available_years' => DB::table('events_data')
                ->selectRaw('YEAR(service_date) as y')
                ->distinct()
                ->orderByDesc('y')
                ->pluck('y')
        ]);
    }

    public function getEventsChartData(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $months = $request->input('months');

        $query = DB::table('events_data')
            ->whereYear('service_date', $year)
            ->where('value', '>', 0)
            ->whereNotNull('service_name');

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

        $events = $data->pluck('event')->unique()->sort()->values();
        $series = [];

        foreach ($events as $event) {
            $monthlyData = array_fill(0, 12, 0);
            foreach ($data->where('event', $event) as $row) {
                $monthlyData[$row->month - 1] = (int)$row->total;
            }

            $series[] = [
                'name' => $event,
                'data' => $monthlyData
            ];
        }

        $availableYears = DB::table('events_data')
            ->whereNotNull('service_date')
            ->selectRaw('YEAR(service_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return response()->json([
            'series' => $series,
            'available_years' => $availableYears,
            'year' => (int)$year
        ]);
    }

public function getGuestChartData(Request $request)
{
    $year = $request->input('year', date('Y'));
    $months = $request->input('months', []);

    try {
        $query = DB::table('events_data')
            ->whereYear('service_date', $year)
            ->where('value', '>', 0)
            ->whereNotNull('service_name');

        if (!empty($months) && is_array($months)) {
            $query->whereIn(DB::raw('MONTH(service_date)'), array_map('intval', $months));
        }

        // Try different approaches
        $data = null;
        
        // Method 1: Try with MONTH()
        try {
            $data = $query->select(
                    'service_name as event',
                    DB::raw('MONTH(service_date) as month'),
                    DB::raw('SUM(value) as total')
                )
                ->groupBy('event', DB::raw('MONTH(service_date)'))
                ->orderBy(DB::raw('MONTH(service_date)'))
                ->get();
        } catch (\Exception $e) {
            // Method 2: Try without MONTH() function
            $data = $query->select(
                    'service_name as event',
                    DB::raw('EXTRACT(MONTH FROM service_date) as month'),
                    DB::raw('SUM(value) as total')
                )
                ->groupBy('event', DB::raw('EXTRACT(MONTH FROM service_date)'))
                ->orderBy(DB::raw('EXTRACT(MONTH FROM service_date)'))
                ->get();
        }

        // Process data
        $events = $data->pluck('event')->unique()->sort()->values();
        $series = [];
        $monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                       "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        foreach ($events as $event) {
            $monthlyData = array_fill(0, 12, 0);
            
            foreach ($data->where('event', $event) as $row) {
                $monthIndex = (int)$row->month - 1;
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $monthlyData[$monthIndex] = (int)$row->total;
                }
            }

            if (!empty($months)) {
                $filteredData = [];
                foreach ($months as $month) {
                    $monthIndex = intval($month) - 1;
                    $filteredData[] = $monthlyData[$monthIndex] ?? 0;
                }
                $monthlyData = $filteredData;
            }

            if (array_sum($monthlyData) > 0) {
                $series[] = [
                    'name' => $event,
                    'data' => $monthlyData
                ];
            }
        }

        $categories = $monthNames;
        if (!empty($months)) {
            $categories = collect($months)
                ->sort()
                ->map(function($m) use ($monthNames) {
                    $index = intval($m) - 1;
                    return $monthNames[$index] ?? '';
                })
                ->filter()
                ->values()
                ->toArray();
        }

        return response()->json([
            'series' => $series,
            'categories' => $categories,
            'available_years' => DB::table('events_data')
                ->selectRaw('YEAR(service_date) as year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->toArray(),
            'year' => (int)$year,
            'success' => true
        ]);

    } catch (\Exception $e) {
        \Log::error('Guest chart error: ' . $e->getMessage());
        
        return response()->json([
            'series' => [],
            'categories' => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                           "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            'available_years' => [2025],
            'year' => (int)$year,
            'error' => $e->getMessage(),
            'success' => false
        ]);
    }
}
}
