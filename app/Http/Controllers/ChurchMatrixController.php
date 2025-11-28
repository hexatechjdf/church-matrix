<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveChurchApiRequest;
use App\Services\ChurchMatrixService;
use Illuminate\Http\Request;
use App\Models\ChurchApi;


class ChurchMatrixController extends Controller
{
    protected $service;

    public function __construct(ChurchMatrixService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $settings = $this->service->getLatestSettings();
        $regions = $settings ? $this->service->fetchRegions($settings) : null;
        $allSettings = ChurchApi::orderBy('id', 'desc')->get();

        return view('admin.church_matrix.index', compact('settings', 'regions', 'allSettings'));
    }


    public function saveApi(SaveChurchApiRequest $request)
    {
        $settings = $this->service->saveApiCredentials($request->validated());
        $regions = $this->service->fetchRegions($settings);

        if (!$regions) {
            return redirect()->route('church-matrix.index')
                ->with('error', 'Invalid email or API key – connection failed!');
        }

        return redirect()->route('church-matrix.index')
            ->with('success', 'Connected successfully!')->with('regions', $regions);
    }




    public function saveRegion(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
        ]);

        $settings = $this->service->saveRegion($request->region_id);

        if (!$settings) {
            return redirect()->route('church-matrix.index')
                ->with('error', 'No API credentials found. Please save API first.');
        }

        return redirect()->route('church-matrix.index')
            ->with('success', 'Region saved successfully!');
    }

    public function saveLocation(Request $request)
    {
        $request->validate([
            'location_id' => 'required|string',
        ]);

        $settings = $this->service->saveLocation($request->location_id);

        if (!$settings) {
            return redirect()->route('church-matrix.index')
                ->with('error', 'No API credentials found. Please save API first.');
        }

        return redirect()->route('church-matrix.index')
            ->with('success', 'Location ID saved successfully!');
    }
}
