<?php

namespace App\Http\Controllers\Location\Planning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Jobs\Planning\SyncEventsDataJob;
use App\Jobs\Planning\SyncPlanningTokensJob;
use App\Services\PlanningService;


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
            $date = $request->date ?? null;
            $fetchAll=false;
            if(!$date){
                $fetchAll = true;
                unset($params['user']);
                unset($params['token']);
                $date = Carbon::yesterday()->toDateString();
            }

            $created = [...$params, 'created' => $date];
            $updated = [...$params, 'updated' => $date];

            if($fetchAll){
                SyncPlanningTokensJob::dispatchSync($created);
                SyncPlanningTokensJob::dispatchSync($updated);
            }else{
                $this->syncCommand($created);
                $this->syncCommand($updated);
            }
        }

        if ($type === 'year') {
            $this->syncCommand($params);
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

    public function testConnection(Request $request,PlanningService $planningService)
    {
       $user = loginUser();
        $token = $user->planningToken;

        if (!$token) {
            return response()->json([
                'status' => false,
                'type'   => 'token_missing',
                'message'=> 'Please connect your Planning Center account first.'
            ], 422);
        }

        try {
            $url = 'check-ins/v2/event_times?include=event,headcounts&per_page=1&offset=1';
            $access_token = $token->access_token;
            $response = $planningService->planning_api_call(
                $url,
                'get',
                '',
                [],
                false,
                $access_token
            );

            if (isset($response->errors)) {
                $errorMessage = $response->errors[0]->detail ?? 'Planning Center API error.';

                if (str_contains(strtolower($errorMessage), 'do not have access')) {
                    return response()->json([
                        'status'  => false,
                        'type'    => 'access_revoked',
                        'message' => 'Failed to load data. Please disconnect your account and reconnect it again to restore access.'
                    ], 403);
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Your Planning Center account is connected correctly.'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'type'   => 'exception',
                'message'=> 'Unable to connect to Planning Center. Please reconnect your account.',
                'debug'  => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}
