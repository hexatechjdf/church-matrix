<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveChurchApiRequest;
use App\Services\ChurchService;
use Illuminate\Http\Request;
use App\Models\ChurchApi;
use App\Models\CrmToken;


class ChurchMatrixController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $settings = getChurchToken();
        $regions =  $settings ? $this->service->fetchRegions($settings) : null;

        return view('admin.church_matrix.index', compact('settings', 'regions'));
    }


    public function saveApi(SaveChurchApiRequest $request)
    {
        $settings = $this->service->saveApiCredentials($request->validated());
        $regions =  $this->service->fetchRegions() ?? null;

        return redirect()->route('church-matrix.index')
            ->with('success', 'Connected successfully!');
    }

    public function saveRegion(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
        ]);

        $this->service->saveRegion($request->region_id);

        return redirect()->route('church-matrix.index')
            ->with('success', 'Region saved successfully!');
    }

    public function saveLocation(Request $request)
    {
        $request->validate([
            'location_id' => 'required|string',
        ]);

        $this->service->saveLocation($request->location_id);

        return redirect()->route('church-matrix.index')
            ->with('success', 'Location ID saved successfully!');
    }
}
