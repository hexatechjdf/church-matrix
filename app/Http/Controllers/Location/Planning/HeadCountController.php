<?php

namespace App\Http\Controllers\Location\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Jobs\Planning\SyncEventsDataJob;


class HeadCountController extends Controller
{
    public function index(Request $request)
    {
       return view('locations.planning.headcounts.index');
    }

    public function fetchLastHeadcounts(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'type' => 'required|in:date,year',
            'date' => 'required_if:type,date|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $type = $request->type;
        $user = loginUser();
        $token = $user->planningToken;

        if(!$token)
        {
            return response()->json([
                'status' => false,
                'message' => 'First Connect to Planning center'
            ], 422);
        }

        $access_token = $token->access_token;

        $params = [
            'user'    => $user->id,
            'token'    => $access_token,
            'offset'   => 0,
            'type'     => $type == 'date' ? 'headcount' : 'events'
        ];


        if ($type === 'date') {

            $date = $request->date ?? Carbon::yesterday()->toDateString();

            $created = [...$params, 'created' => $date];
            $updated = [...$params, 'updated' => $date];
            $this->syncCommand($created);

            $this->syncCommand($updated);

        }

        if ($type === 'year') {
            $year = (int) $request->year;
            $from = Carbon::create($year, 1, 1)->startOfDay();
            $to   = Carbon::create($year, 12, 31)->endOfDay();
            $created = [...$params, 'created' => $from, 'created_to' => $to];

            $this->syncCommand($created);
        }

            return response()->json([
                'status' => true,
                'message' => 'Request has been sent. Data will be fetched soon'
            ], 200);
    }

    public function syncCommand($params = [])
    {
        SyncEventsDataJob::dispatchSync($params);
        // $options = $this->buildCommandOptions($params);

        // \Artisan::call('events_data:sync', $options);
    }

    public function buildCommandOptions(array $params): array
    {
        $options = [];

        foreach ($params as $key => $value) {

            if (is_bool($value)) {
                if ($value === true) {
                    $options["--{$key}"] = true;
                }
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $options["--{$key}"] = $value;
        }

        return $options;
    }

}
