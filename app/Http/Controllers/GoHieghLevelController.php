<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoHieghLevelController extends Controller
{
    public function callback(Request $request)
    {
        save_logs($request);
        ghl_token($request->code??"");
    }
}
