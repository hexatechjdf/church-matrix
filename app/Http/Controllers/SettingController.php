<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Helpers\CRM;
use Hash;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {

        $settings = Setting::pluck('value', 'key')->toArray();
        $scopes = CRM::$scopes;
        $company_name = null;
        $connecturl = CRM::directConnect();
        $company_id = null;
        $authuser = loginUser();
        $crmauth = $authuser->crmtoken;

        // try {
        //     if (@$crmauth->company_id) {
        //         list($company_name, $company_id) = CRM::getCompany($authuser,'Company');

        //     }
        // } catch (\Exception $e) {

        // }

        return view('admin.setting.index', get_defined_vars());
    }

    public function save(Request $request, $id = null)
    {
        $user = loginUser();
        foreach ($request->setting ?? [] as $key => $value) {

            save_setting($key, $value,$user->id);
        }

        return back()->withSuccess("Setting Saved !");
    }
}
