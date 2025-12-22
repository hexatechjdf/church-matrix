<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\DialController;
use App\Http\Controllers\GoHieghLevelController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\PlanningCenterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Location\Churchmatrix\IndexController;
use App\Http\Controllers\Location\Planning\PlanningController;
use App\Http\Controllers\Location\Planning\HeadCountController;
use App\Http\Controllers\Location\Planning\ChartController;
use App\Http\Controllers\Location\AutoAuthController;
use App\Http\Controllers\ChurchMatrixController;
use App\Http\Controllers\Location\Churchmatrix\TimeZoneController;
use App\Http\Controllers\Location\Churchmatrix\ChurchEventController;
use App\Http\Controllers\Location\Churchmatrix\RecordController;
use App\Http\Controllers\Location\Churchmatrix\ServiceTimeController;
use App\Http\Controllers\Location\Churchmatrix\SettingIntergration;
use App\Http\Controllers\Location\Churchmatrix\StatsController;
use App\Models\Locations;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/cache', function () {
    \Artisan::call("optimize:clear");
    \Artisan::call("cache:clear");
    \Artisan::call("config:clear");
});

Route::get('testwebhook/{id}', function ($id) {
    //
    $res = \App\Models\Setting::where(['key' => 'planning_organization_id', 'value' => $id])->first();
    //\DB::table('logs')->insert(['message'=>$res]);


    if (!$res) {
        echo 'Unable To connect';
        die;
    }
    request()->user_id = $res->location_id;
    $dt = planning_api_call('webhooks/v2/batch_update', 'POST', '{
    "data": {
        "attributes": {
            "url": "https://chrchfnnls.com/api/planning-lead-capture",
            "old_url": "",
            "names": [
                "people.v2.events.person.created",
                "people.v2.events.person.updated"
            ]
        }
    }
}');
    dd($dt);
});

Route::get('/hitapiloc', function () {
    $client = new Client();
    $headers = [
        'Authorization' => 'Bearer b2858b8a-5684-4fae-a951-68f3de65699f'
    ];
    $request = new Psr7Request('POST', 'https://services.msgsndr.com/oauth/authorize?client_id=622eec05cb0d702f905aa5e8-l0qdiicz&location_id=NP4dT88lEnnjb3WVmyAQ&response_type=code&redirect_uri=https://oauth.requestcatcher.com/test/myid&scope=conversations/message.write&userType=Location', $headers);
    $res = $client->sendAsync($request)->wait();
    echo $res->getBody()->getContents();
});


require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/profile-save', [DashboardController::class, 'general'])->name('profile.save');
    Route::post('/password-save', [DashboardController::class, 'password'])->name('password.save');


    //users
    Route::prefix('users')->name('user.')->group(function () {
        $controller = UserController::class;
        Route::get('/list', [$controller, 'list'])->name('list');
        Route::get('/add', [$controller, 'add'])->name('add');
        Route::get('/edit/{id?}', [$controller, 'edit'])->name('edit');
        Route::post('/save/{id?}', [$controller, 'save'])->name('save');
        Route::get('/delete/{id?}', [$controller, 'delete'])->name('delete');
        Route::get('/webhook-url/{id?}', [$controller, 'showUrls'])->name('showUrls');
        Route::get('/is-active/{id?}', [$controller, 'isActive'])->name('is-active');
        Route::get('/imports', [$controller, 'importUsers'])->name('import');
    });

    //Setting

    Route::prefix('locations')->name('locations.')->group(function () {
        $controller = IndexController::class;
        Route::get('/list', [$controller, 'list'])->name('list');
    });
});



// GHL Oauth
Route::prefix('crm')->name('crm.')->group(function () {
    // Route::get('/callback', [GoHieghLevelController::class, 'callback'])->name('callback');
    Route::get('/callback', [GoHieghLevelController::class, 'callback'])->name('oauth_callback');
});

Route::prefix('locations')->name('locations.')->group(function () {
    $controller = IndexController::class;
    Route::get('/list', [$controller, 'list'])->name('list');
});

Route::prefix('settings')->name('setting.')->group(function () {
    $controller = SettingController::class;
    Route::get('/add', [$controller, 'add'])->name('add');
    Route::post('/save/{id?}', [$controller, 'save'])->name('save');
});


Route::prefix('church-matrix')->name('church-matrix.')->group(function () {
    Route::get('/', [ChurchMatrixController::class, 'index'])->name('index');
    Route::get('/request/listing', [ChurchMatrixController::class, 'requestlisting'])->name('request.listing');
    Route::post('/accpet-request/{id}', [ChurchMatrixController::class, 'acceptRequest'])->name('accept.request')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/test-request/{id}', [ChurchMatrixController::class, 'testRequest'])->name('test.request')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/save-api', [ChurchMatrixController::class, 'saveApi'])->name('save-api')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/save-location', [ChurchMatrixController::class, 'saveLocation'])->name('save-location')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/save-region', [ChurchMatrixController::class, 'saveRegion'])->name('save-region')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/save-location', [ChurchMatrixController::class, 'saveLocation'])->name('save-location')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/save-timezone', [ChurchMatrixController::class, 'saveTimezone'])->name('save-timezone')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});



// Planning Center Connection Routes
Route::prefix('planning-center')->name('planningcenter.')->group(function () {
    $controller = PlanningController::class;
    Route::get('/callback', [$controller, 'callback'])->name('callback');
});


// Testing Routes
Route::get('/get-people', [PlanningController::class, 'getContact'])->name('contacts');

// Route::get('/post-people', [PlanningCenterController::class, 'capturelead'])->name('capturelead');


// Route::get('/test', function () {
//     $client = new Client();
//     $headers = [];
//     $request = new Psr7Request('GET', 'https://stoplight.io/api/v1/projects/highlevel/integrations/nodes/locations/locations.json?fromExportButton=true&snapshotType=http_service', $headers);
//     $res = $client->sendAsync($request)->wait();
//     $var = collect(collect(collect(json_decode($res->getBody()->getContents())->paths)->first())->first()->parameters)->where('name', 'Version')->first()->schema->example;

//     dd($var);
// });

Route::prefix('locations')->name('locations.')->group(function () {
    Route::prefix('churchmatrix')->name('churchmatrix.')->group(function () {
        $controller = IndexController::class;
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/user-campus-form', [$controller, 'getUserCampusForm'])->name('getUserCampusForm');
        Route::post('/save-user-campus', [$controller, 'saveUserCampusAjax'])->name('saveUserCampusAjax')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        Route::prefix('setting-intergration')->name('setting-intergration.')->group(function () {
            Route::get('/', [SettingIntergration::class, 'index'])->name('index');
        });

        Route::prefix('integration')->name('integration.')->group(function () {

            Route::get('/get/events', [SettingIntergration::class, 'getEvents'])->name('events');
            Route::get('/get/campuses', [SettingIntergration::class, 'getCampuses'])->name('campuses');
            Route::get('/get/times', [SettingIntergration::class, 'getServiceTimes'])->name('times');

            Route::get('/get/categories', [SettingIntergration::class, 'getCategories'])->name('categories');


            Route::get('/update/times', [SettingIntergration::class, 'updateTimes'])->name('update.times');
            Route::get('/update/records', [SettingIntergration::class, 'updateRecords'])->name('update.records');

            Route::prefix('events')->name('events.')->group(function () {
                Route::get('/', [ChurchEventController::class, 'index'])->name('index');
                Route::get('/get/events', [ChurchEventController::class, 'getEvents'])->name('data');
                Route::post('/manage', [ChurchEventController::class, 'manage'])->name('manage')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
                Route::post('destroy/{id}', [ChurchEventController::class, 'destroy'])->name('destroy')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            });

            Route::prefix('service-times')->name('service-times.')->group(function () {
                Route::get('/', [ServiceTimeController::class, 'index'])->name('index');
                Route::get('/get/times', [ServiceTimeController::class, 'getTimes'])->name('data');
                Route::get('/get/form', [ServiceTimeController::class, 'getForm'])->name('form');
                Route::post('/manage', [ServiceTimeController::class, 'manage'])->name('manage')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
                Route::post('destroy/{id}', [ServiceTimeController::class, 'destroy'])->name('destroy')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            });

            Route::prefix('records')->name('records.')->group(function () {
                Route::get('/', [RecordController::class, 'index'])->name('index');
                Route::get('/get/form', [RecordController::class, 'getForm'])->name('form');
                Route::get('/get/service-times', [RecordController::class, 'getTimesPaginated'])->name('service-times');
                Route::post('/manage', [RecordController::class, 'manage'])->name('manage')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
                Route::post('destroy/{id}', [RecordController::class, 'destroy'])->name('destroy')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            });

            Route::prefix('stats')->name('stats.')->group(function () {
                Route::get('/', [StatsController::class, 'index'])->name('index');
                Route::get('/by/month/', [StatsController::class, 'timesChartData'])->name('month');
                Route::get('/by/week/', [StatsController::class, 'getWeekStats'])->name('week');
            });
        });
    });

    Route::prefix('planningcenter')->name('planningcenter.')->group(function () {
        $c = PlanningController::class;
        Route::get('/', [$c, 'index'])->name('index');
        Route::get('/get/settings', [$c, 'getPlanningSettings'])->name('get.settings');
        Route::get('events', [PlanningCenterController::class, 'getEvents'])->name('events');


        Route::get('workflow/saved', [$c, 'saveWorkflow'])->name('saveWorkflow');
        Route::get('listworkflows', [$c, 'listworkflows'])->name('listworkflows');
        Route::get('disconnectplanning', [$c, 'disconnectplanning'])->name('disconnectplanning');

        Route::prefix('headcounts')->name('headcounts.')->group(function () {
            Route::get('/', [HeadCountController::class, 'index'])->name('index');
        });

        Route::get('/event-filter', [ChartController::class, 'index'])->name('event.filter');
        Route::get('/get-chart-json', [ChartController::class, 'getChartJson'])->name('chart.json');
        Route::get('/get-pie-chart-data', [ChartController::class, 'getPieChartData'])->name('pie.chart.data');
        Route::get('/get-events-chart-data', [ChartController::class, 'getEventsChartData'])->name('events.chart.data');
        Route::get('/get-line-chart-data', [ChartController::class, 'getLineChartData'])->name('line.chart.data');

        Route::get('/get-guest-chart-data', [ChartController::class, 'getGuestChartData'])->name('guest.chart.data');

        Route::get('/headcounts/visualization', [$c, 'headCountSetting'])->name('visuals');
    });
});



Route::get('/test/eventtimes', [PlanningController::class, 'eventtimes']);
Route::get('check/auth', [AutoAuthController::class, 'connect'])->name('auth.check');
Route::get('checking/auth', [AutoAuthController::class, 'authChecking'])->name('auth.checking');
Route::get('planning', [DashboardController::class, 'planning'])->name('auth.planning');
Route::get('check/auth/error', [AutoAuthController::class, 'authError'])->name('error');

Route::get('/fetch/headcounts', [HeadCountController::class, 'fetchLastHeadcounts'])->name('fetch.headcounts');
Route::get('/test/planningcenter/connection', [HeadCountController::class, 'testConnection'])->name('test.planningcenter.connection');

Route::get('/ll', function () {
    Auth::loginUsingId(1);
    return redirect()->route('dashboard');
});


Route::get('/tokens_renew', [PlanningController::class, 'updateTokens'])->name('tokens_renew');


Route::get('/get-charts', function () {
    return view('charts');
});

use App\Services\PlanningService;
use App\Models\CrmToken;

Route::get('/get-head-counts', function (PlanningService $service) {
    $t = CrmToken::where('id', 15)->first();
    $planning = @$t->access_token;

    $request = new Request();
    $request->merge([
        'user_id' => 883,

    ]);
    $w = $service->planning_api_call('check-ins/v2/event_times?include=event,headcounts&per_page=50', 'get', '', [], false, $planning);

    dd($w);
});

Route::get('/get-attendance-type', function (PlanningService $service) {
    $t = CrmToken::where('id', 5)->first();
    $planning = @$t->access_token;

    $request = new Request();
    $request->merge([
        'user_id' => 883,
    ]);
    $w = $service->planning_api_call('check-ins/v2/headcounts?include=attendance_type,event_time&order=created_at&where[created_at]=2025-11-28', 'get', '', [], false, $planning);

    dd($w);
});

//https://api.planningcenteronline.com/check-ins/v2/headcounts?include=attendance_type,event_time&order=created_at&where[created_at]=2025-12-01
//https://api.planningcenteronline.com/check-ins/v2/headcounts?include=attendance_type,event_time&order=created_at&where[updated_at]=2025-12-01

Route::get('/reconnect-pco-with-checkins', function () {
    return redirect('https://api.planningcenteronline.com/oauth/authorize?' . http_build_query([
        'client_id'     => getAccessToken('planning_client_id'),
        'redirect_uri'  => 'http://localhost:8000/pco-final-callback',
        'response_type' => 'code',
        'scope'         => 'check_ins',           // ← YE ZAROORI HAI
        'state'         => 'user883',
    ]));
});

Route::get('/pco-final-callback', function () {
    $code = request('code');
    if (!$code) return 'Denied!';

    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://api.planningcenteronline.com/oauth/token', [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'client_id'     => getAccessToken('planning_client_id'),
        'client_secret' => getAccessToken('planning_client_secret'),
        'redirect_uri'  => 'http://localhost:8000/pco-final-callback',
    ]);

    $result = $response->json();

    if (isset($result['access_token'])) {
        \App\Models\CrmToken::updateOrCreate(
            ['user_id' => 883, 'crm_type' => 'planning'],
            [
                'access_token'  => $result['access_token'],
                'refresh_token' => $result['refresh_token'] ?? null,
                'crm_type'      => 'planning',
            ]
        );

        return "<h1 style='color:green'>PERFECT! Check-Ins scope ke saath tokens save ho gaye!</h1>
                <h2>Ab http://localhost:8000/get-head-counts chalao → 100% data aayega!</h2>";
    }

    return 'Failed: <pre>' . print_r($result, true) . '</pre>';
});

Route::get('/connect-pco-final', function () {
    $registeredRedirectUri = 'http://localhost:8000/planning-center/callback'; // YE BILKUL WAHI HONA CHAHIYE JO DEVELOPER APP MEIN HAI

    return redirect('https://api.planningcenteronline.com/oauth/authorize?' . http_build_query([
        'client_id'     => getAccessToken('planning_client_id'),
        'redirect_uri'  => $registeredRedirectUri,
        'response_type' => 'code',
        'scope'         => 'check_ins',        // YE ZAROORI HAI
        'state'         => 'user883',
    ]));
});

Route::get('/planning-center/callback', function () {
    $code = request('code');
    if (!$code) return 'Access denied.';

    $registeredRedirectUri = 'http://localhost:8000/planning-center/callback'; // SAME AS ABOVE

    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://api.planningcenteronline.com/oauth/token', [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'client_id'     => getAccessToken('planning_client_id'),
        'client_secret' => getAccessToken('planning_client_secret'),
        'redirect_uri'  => $registeredRedirectUri,
    ]);

    $result = $response->json();

    if (isset($result['access_token'])) {
        \App\Models\CrmToken::updateOrCreate(
            ['user_id' => 883, 'crm_type' => 'planning'],
            [
                'access_token'  => $result['access_token'],
                'refresh_token' => $result['refresh_token'] ?? null,
                'crm_type'      => 'planning',
            ]
        );

        return "<h1 style='color:green; text-align:center'>
                SUCCESS! Check-Ins permission ke saath tokens save ho gaye!
                </h1>
                <h2 style='text-align:center'>
                Ab jaao: <a href='/get-head-counts'>/get-head-counts</a> → Real data aayega!
                </h2>";
    }

    return 'Failed: <pre>' . print_r($result, true) . '</pre>';
})->name('planningcenter.callback');

Route::get('/final-pco-connect', function () {
    return redirect('https://api.planningcenteronline.com/oauth/authorize?' . http_build_query([
        'client_id'     => '35cdf38061f0f8276d063c68883b9ee215b1f026ab1360898004f5af0b1514a0',
        'redirect_uri'  => 'http://localhost:8000/planning-center/callback',
        'response_type' => 'code',
        'scope'         => 'check_ins',
        'state'         => 'user883',
    ]));
});

Route::get('/planning-center/callback', function () {
    $code = request('code');
    if (!$code) return 'Denied';

    // YE CLIENT SECRET BILKUL SAHI HONA CHAHIYE
    $clientSecret = 'eb90446d61a728fdfb22b58649c69c47f43c88c572523a3c8f7ba4ef56ea88f1';

    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://api.planningcenteronline.com/oauth/token', [
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'client_id'     => '35cdf38061f0f8276d063c68883b9ee215b1f026ab1360898004f5af0b1514a0',
        'client_secret' => $clientSecret,    // YE SAHI HONA CHAHIYE
        'redirect_uri'  => 'http://localhost:8000/planning-center/callback',
    ]);

    $result = $response->json();

    if (isset($result['access_token'])) {
        \App\Models\CrmToken::updateOrCreate(
            ['user_id' => 883, 'crm_type' => 'planning'],
            [
                'access_token'  => $result['access_token'],
                'refresh_token' => $result['refresh_token'] ?? null,
                'crm_type'      => 'planning',
            ]
        );

        return "<h1 style='color:green'>FULL SUCCESS! Check-Ins + Tokens Saved!</h1>
                <h2><a href='/get-head-counts'>/get-head-counts</a></h2>";
    }

    return 'Failed: <pre>' . print_r($result, true) . '</pre>';
});
