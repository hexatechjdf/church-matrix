<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Hash;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function add()
    {
        $setting = Setting::first();
        return view('admin.setting.add', get_defined_vars());
    }

    public function save(Request $request, $id = null)
    {
        foreach ($request->except('_token') as $key => $value) {
            save_setting($key, $value,1);
        }

        return back()->withSuccess("Setting Saved !");
    }
}
