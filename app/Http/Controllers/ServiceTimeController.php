<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceTimeController extends Controller
{
    public function index()
    {
        return view('service_times.index');
    }
}
