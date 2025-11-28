@extends('layouts.app')

@section('title', 'Service time')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="float-right">
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addEventModal">
                    <i class="fas fa-plus"></i> Add Service time
                </button>
            </div>
            <h4 class="page-title">Service times List</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">All Service times</h5>
    </div>

    <div class="card-body">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
    <thead class="thead-dark">
        <tr>
            <th>#</th>
            <th>campus_id</th>
            <th>day_of_week</th>
            <th>time_of_day</th>
            <th>timezone</th>
            <th>relation_to_sunday</th>
            <th>date_start</th>
            <th>date_end</th>
            <th>replaces</th>
            <th>event_id</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>101</td>
            <td>Monday</td>
            <td>09:00 AM</td>
            <td>PST</td>
            <td>Before</td>
            <td>2025-12-01</td>
            <td>2025-12-01</td>
            <td>None</td>
            <td>E001</td>
        </tr>
        <tr>
            <td>2</td>
            <td>102</td>
            <td>Wednesday</td>
            <td>11:00 AM</td>
            <td>EST</td>
            <td>After</td>
            <td>2025-12-03</td>
            <td>2025-12-03</td>
            <td>Old Event 1</td>
            <td>E002</td>
        </tr>
        <tr>
            <td>3</td>
            <td>103</td>
            <td>Friday</td>
            <td>02:00 PM</td>
            <td>CST</td>
            <td>Same</td>
            <td>2025-12-05</td>
            <td>2025-12-05</td>
            <td>Old Event 2</td>
            <td>E003</td>
        </tr>
        <tr>
            <td>4</td>
            <td>104</td>
            <td>Sunday</td>
            <td>06:00 PM</td>
            <td>MST</td>
            <td>Before</td>
            <td>2025-12-07</td>
            <td>2025-12-07</td>
            <td>None</td>
            <td>E004</td>
        </tr>
    </tbody>
</table>


    </div>
</div>

@include('service_times.add')

@endsection
