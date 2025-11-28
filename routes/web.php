<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CampaignsController;
use App\Http\Controllers\DialController;
use App\Http\Controllers\GoHieghLevelController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\PlanningCenterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Location\IndexController;
use App\Http\Controllers\Location\AutoAuthController;
use App\Http\Controllers\ChurchMatrixController;
use App\Http\Controllers\TimeZoneController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ServiceTimeController;
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

 Route::prefix('settings')->name('setting.')->group(function () {
        $controller = SettingController::class;
        Route::get('/add', [$controller, 'add'])->name('add');
        Route::post('/save/{id?}', [$controller, 'save'])->name('save');
    });

      // GHL Oauth
   Route::prefix('crm')->name('crm.')->group(function () {
        // Route::get('/callback', [GoHieghLevelController::class, 'callback'])->name('callback');
        Route::get('/callback', [GoHieghLevelController::class, 'callback'])->name('oauth_callback');
    });
// });

Route::prefix('settings')->name('setting.')->group(function () {
    $controller = SettingController::class;
    Route::get('/add', [$controller, 'add'])->name('add');
    Route::post('/save/{id?}', [$controller, 'save'])->name('save');
});


Route::prefix('church-matrix')->name('church-matrix.')->group(function () {
    Route::get('/', [ChurchMatrixController::class, 'index'])->name('index');
    Route::post('/save-api', [ChurchMatrixController::class, 'saveApi'])->name('save-api');
    Route::post('/save-location', [ChurchMatrixController::class, 'saveLocation'])->name('save-location');
    Route::post('/save-region', [ChurchMatrixController::class, 'saveRegion'])->name('save-region');
    Route::post('/save-location', [ChurchMatrixController::class, 'saveLocation'])->name('save-location');
});


Route::prefix('church-matrix-settings')->group(function () {
    Route::get('/timezone', [TimeZoneController::class, 'showTimezoneForm'])->name('settings.timezone');
    Route::post('/timezone', [TimeZoneController::class, 'saveTimezone'])->name('settings.timezone.save');
});


Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::post('/store', [EventController::class, 'store'])->name('store');
});


Route::prefix('service-times')->name('service-times.')->group(function () {
    Route::get('/', [ServiceTimeController::class, 'index'])->name('index');
    Route::get('/create', [ServiceTimeController::class, 'create'])->name('create');
    Route::post('/store', [ServiceTimeController::class, 'store'])->name('store');
});


Route::prefix('records')->name('records.')->group(function () {
    Route::get('/', [RecordController::class, 'index'])->name('index');
    Route::get('/create', [RecordController::class, 'create'])->name('create');
    Route::post('/store', [RecordController::class, 'store'])->name('store');
});



// GHL Oauth
Route::prefix('crm')->name('crm.')->group(function () {
    Route::get('/callback', [GoHieghLevelController::class, 'callback'])->name('callback');
});


// Planning Center Connection Routes
Route::prefix('planning-center')->name('planningcenter.')->group(function () {
    $controller = PlanningCenterController::class;
    Route::get('/callback', [$controller, 'callback'])->name('callback');
});


// Testing Routes
Route::get('/get-people', [PlanningCenterController::class, 'getContact'])->name('contacts');

Route::get('/post-people', [PlanningCenterController::class, 'capturelead'])->name('capturelead');


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
    });
});


Route::get('check/auth', [AutoAuthController::class, 'connect'])->name('auth.check');
// Route::get('check/auth', [DashboardController::class, 'authCheck'])->name('auth.check');
Route::get('checking/auth', [AutoAuthController::class, 'authChecking'])->name('auth.checking');
// Route::get('checking/auth', [DashboardController::class, 'authChecking'])->name('auth.checking');
Route::get('workflow/saved', [DashboardController::class, 'saveWorkflow'])->name('auth.saveWorkflow');
Route::get('planning', [DashboardController::class, 'planning'])->name('auth.planning');
Route::get('disconnectplanning', [DashboardController::class, 'disconnectplanning'])->name('auth.disconnectplanning');
Route::get('listworkflows', [DashboardController::class, 'listworkflows'])->name('auth.listworkflows');


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
