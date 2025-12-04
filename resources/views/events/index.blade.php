@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="float-right">
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addEventModal">
                    <i class="fas fa-plus"></i> Add Event
                </button>
            </div>
            <h4 class="page-title">Events List</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">All Events</h5>
    </div>

    <div class="card-body">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>created_at</th>
                </tr>
            </thead>

             <tbody>
                <tr>
                    <td>1</td>
                    <td>Jack</td>
                    <td>2025-12-01</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Tom</td>
                    <td>2025-12-25</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Json</td>
                    <td>2025-04-20</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Array</td>
                    <td>2025-11-30</td>
                </tr>
            </tbody>

        </table>

    </div>
</div>

@include('events.add')

@endsection
