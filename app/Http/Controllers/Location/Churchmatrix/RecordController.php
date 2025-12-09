<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ChurchService;
use Carbon\Carbon;

class RecordController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $campuses   = $this->service->fetchCampuses();
        $events     = $this->service->fetchEvents();
        $categories = $this->service->fetchCategories();

        if ($request->ajax()) {
            $records = DB::table('church_records')->orderBy('id', 'DESC');

            return DataTables::of($records)
                ->editColumn('service_date_time', fn($r) => Carbon::parse($r->service_date_time)->format('d M Y'))
                ->make(true);
            // Actions column ab blade mein banega → controller clean rahega
        }

        return view('locations.churchmatrix.records.index', compact('campuses', 'events', 'categories'));
    }
}
