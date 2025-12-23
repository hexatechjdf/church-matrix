<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ChurchService;
use App\Jobs\churchmatrix\ServiceTimeJob;
use Carbon\Carbon;
use App\Models\ServiceTime;
use App\Models\ChurchRecord;
use App\Jobs\churchmatrix\SendCategoryValueToApi;

class RecordController extends Controller
{
    protected $service;

    public function __construct(ChurchService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = loginUser();

        if ($request->ajax()) {

           $records = ChurchRecord::when(!$user->church_admin,function($q)use($user){
                $campus = @$user->campus;
                $q->where('campus_unique_id', $campus->campus_unique_id);
            })->when($user->church_admin,function($q)use($user){
                $q->where('user_id', $user->id);
            })->orderBy('id', 'DESC');

            return DataTables::of($records)
                ->editColumn('service_date_time', fn($r) => Carbon::parse($r->service_date_time)->format('d M Y'))
                ->editColumn('year', function ($r) {
                    return explode('_', @$r->week_volume)[0] ?? '';
                })

                ->make(true);
        }

        return view('locations.churchmatrix.records.index',get_defined_vars());
    }

    public function getForm(Request $request)
    {
        $user = loginUser();
        $categories = $this->service->fetchCategories();
        $id = $request->id;
        $selectedCategoryId = null;
        $selectedValue = null;
        $payload = null;
        if ($id) {
            $payload = ChurchRecord::where('record_unique_id', $id)->first();
            $selectedCategoryId = $payload->category_unique_id;
            $selectedValue = $payload->value;
        }


        return view('locations.churchmatrix.records.form',get_defined_vars())->render();
    }

    public function getServiceTime($user,$id)
    {
        if (!$user->church_admin) {
            $r =  ServiceTime::where('id',$id)->first();
            return $r ? $r->toArray() : [];
        }else{
            $all = $this->service->getCacheTimes($user);

            $record = collect($all)->first(function($item) use ($id) {
                $id_match = $id ? ($item['cm_id'] == $id) : true;
                return $id_match;
            });

            return $record;
        }
    }

    public function manage(Request $request)
    {
        $user = loginUser();
        $id   = $request->record_id;


        $campus_id = $this->service->getUserCampusId($request,$user);
        try{
            if(!$id)
            {
                $data  =$this->createApiData($request,$user,$campus_id);
            }else{
                $data  =$this->updateApiData($request,$user,$id,$campus_id);
            }
            if (!empty($data['errors'])) {
                $er = $this->handleErrors($data);
                if($er)
                {
                    $id = $this->updateApiData($request,$user,$er,$campus_id);
                }else{
                    return response()->json([
                            'status' => false,
                            'errors' => $data['errors']
                        ], 422);
                }
            }

            if(@$data['id'] && !$id)
            {
                $d = $this->service->setRecordData($user->id,$data);
                if (!empty($d)) {
                    DB::table('church_records')->insert($d);
                }
            }
            if($id)
            {
                ChurchRecord::where('record_unique_id',$id)->update(['value' => $request->value]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Successfully Submitted!',
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'errors' => ['server error']
            ], 422);
        }
    }

    public function createApiData($request,$user,$campus_id)
    {
        $event_id        = $request->event_id;
        $service_time_id = $request->service_time_id;
        $category_id     = $request->category_id;
        $value           = $request->value;
        // $serviceTimezone = "Central Time (US & Canada)";

        $t = $this->getServiceTime($user,$service_time_id);


        $servicetime = @$t['complete_time'] ?? now()->toISOString();

        $data = [
            "category_id"       =>  $category_id,
            "campus_id"         =>  $campus_id,
            "service_time_id"   =>  $service_time_id,
            "value"             =>  $value,
            "replaces"          =>  true,
            "event_id"          =>  $event_id,
            "service_date_time"          =>  $servicetime,
        ];

        // $id ? 'records/'.$id.'.json' :
       return   $this->service->request('POST', 'records.json', $data,false, null,true);
    }

    public function updateApiData($request,$user,$id,$campus_id)
    {
        $value           = $request->value;
        $campus_id = $this->service->getUserCampusId($request,$user);

        $data = [
            "value"             =>  $value,
        ];

        return  $this->service->request('PUT', 'records/'.$id.'.json', $data,false, null,true);
    }

    public function handleErrors($response)
    {
        if (isset($response['errors'])) {
            $errors = $response['errors'];
            $errorString = implode(" ", array_map(fn($e) => $e[0], $response['errors']));

            if (stripos($errorString, "already exists") !== false) {
                preg_match('/with id (\d+)/i', $errorString, $match);
                return  $match[1] ?? null;
            }

            return false;
        }
    }


    public function destroy($cm_id)
    {
        $data = $this->service->request('DELETE','records/'.$cm_id.'.json', [], false,null,true);

        if (!empty($data['errors'])) {
            return response()->json([
                'success' => false,
                'message' => 'Server error!',
            ]);
        }

        ChurchRecord::where('record_unique_id',$cm_id)->delete();
        return response()->json([
             'success' => true,
             'message' => 'Deletd on Church Metrics!',
        ]);
    }


}
