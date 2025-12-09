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
use App\Http\Controllers\Location\AutoAuthController;
use App\Http\Controllers\ChurchMatrixController;
use App\Http\Controllers\Location\Churchmatrix\TimeZoneController;
use App\Http\Controllers\Location\Churchmatrix\ChurchEventController;
use App\Http\Controllers\Location\Churchmatrix\RecordController;
use App\Http\Controllers\Location\Churchmatrix\ServiceTimeController;
use App\Http\Controllers\Location\Churchmatrix\SettingIntergration;
use App\Http\Controllers\ChartController;
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



Route::get('/charts', [ChartController::class, 'index']);
Route::get('/charts/data', [ChartController::class, 'getChartData']);



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

        Route::prefix('setting-intergration')->name('setting-intergration.')->group(function () {
            Route::get('/', [SettingIntergration::class, 'index'])->name('index');
        });

        Route::prefix('integration')->name('integration.')->group(function () {
            Route::prefix('events')->name('events.')->group(function () {
                Route::get('/', [ChurchEventController::class, 'index'])->name('index');
                Route::get('/get/events', [ChurchEventController::class, 'getEvents'])->name('data');
                Route::post('/manage', [ChurchEventController::class, 'manage'])->name('manage')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
                Route::post('destroy/{id}', [ChurchEventController::class, 'destroy'])->name('destroy')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            });

            Route::prefix('service-times')->name('service-times.')->group(function () {
                Route::get('/', [ServiceTimeController::class, 'index'])->name('index');
                Route::get('/get/times', [ServiceTimeController::class, 'getTimes'])->name('data');
                Route::post('/manage', [ServiceTimeController::class, 'manage'])->name('manage')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
                Route::post('destroy/{id}', [ServiceTimeController::class, 'destroy'])->name('destroy')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            });

            Route::prefix('records')->name('records.')->group(function () {
                Route::get('/', [RecordController::class, 'index'])->name('index');
                Route::get('/create', [RecordController::class, 'create'])->name('create');
                Route::post('/store', [RecordController::class, 'store'])->name('store');
            });
        });
    });

    Route::prefix('planningcenter')->name('planningcenter.')->group(function () {
        $c = PlanningController::class;
        Route::get('/', [$c, 'index'])->name('index');
        Route::get('/get/settings', [$c, 'getPlanningSettings'])->name('get.settings');
        Route::get('workflow/saved', [$c, 'saveWorkflow'])->name('saveWorkflow');
        Route::get('listworkflows', [$c, 'listworkflows'])->name('listworkflows');
        Route::get('disconnectplanning', [$c, 'disconnectplanning'])->name('disconnectplanning');


        Route::get('/headcounts/visualization', [$c, 'headCountGraphs'])->name('headcount.visuals');
    });
});


Route::get('/test/eventtimes', [PlanningController::class, 'eventtimes']);

Route::get('check/auth', [AutoAuthController::class, 'connect'])->name('auth.check');
// Route::get('check/auth', [DashboardController::class, 'authCheck'])->name('auth.check');
Route::get('checking/auth', [AutoAuthController::class, 'authChecking'])->name('auth.checking');
// Route::get('checking/auth', [DashboardController::class, 'authChecking'])->name('auth.checking');

Route::get('planning', [DashboardController::class, 'planning'])->name('auth.planning');


// Route::get('check/auth', [AutoAuthController::class, 'connect'])->name('auth.check');
Route::get('check/auth/error', [AutoAuthController::class, 'authError'])->name('error');
// Route::get('checking/auth', [AutoAuthController::class, 'authChecking'])->name('auth.checking');

Route::get('/ll', function () {
    Auth::loginUsingId(1);
    return redirect()->route('dashboard');
});

// Cron Job
Route::get('/tokens_renew', function () {

    tokens_renew();
})->name('tokens_renew');




// use App\Models\ChurchEvent;
// use App\Services\ChurchEventService;

// Route::get('/sync-events', function(ChurchEventService $service){
//     $events = ChurchEvent::whereNull('cm_id')->get();
//     foreach($events as $event){
//         $cm_id = $service->createEventToAPI($event->name);
//         if($cm_id){
//             $event->update(['cm_id' => $cm_id]);
//         }
//     }
//     return "Sync completed!";
// });

use App\Services\PlanningService;
use App\Models\CrmToken;


Route::get('/get-head-counts', function (PlanningService $service) {
    $t = CrmToken::where('id', 5)->first();
    $planning = @$t->access_token;

    $request = new Request();
    $request->merge([
        'user_id' => 886,
        // agar aur fields chahiye to yahan add kar dein
    ]);
    $w = $service->planning_api_call('check-ins/v2/event_times?include=event,headcounts', 'get', '', [], false, $planning);

    dd($w);
});
