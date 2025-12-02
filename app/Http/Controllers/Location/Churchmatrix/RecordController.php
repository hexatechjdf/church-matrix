<?php

namespace App\Http\Controllers\Location\Churchmatrix;

use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function index()
    {
        return view('records.index');
    }
}
