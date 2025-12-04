@extends('layouts.app')

@section('title', 'Setting')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Settings</h4>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Timezone Section --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: #E9F0F0">
                    <h5>Timezone</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.timezone.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="timezone">Select Timezone</label>
                            <select name="timezone" id="timezone" class="form-control" required>
                                <option value="">-- Choose Timezone --</option>
                                @foreach ($timezones as $tz => $label)
                                    <option value="{{ $tz }}"
                                        {{ ($user->timezone ?? '') == $tz ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Save Timezone</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="card shadow-sm" style="width: 25%">
                <div class="card-header text-white" style="background:#87bbce">
                    <h5>Do you want to be Church Matrix Admin?</h5>
                </div>
            </div>
    <div class="col-lg-12 mb-4">
        <div class="row">
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-md-6">

                        <!-- Static API Form -->
                        <form action="#">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Church Matrix API</h5>
                                </div>
                                <div class="card-body">

                                    <div class="form-group">
                                        <label>User Auth (Email) <span class="text-danger">*</span></label>
                                        <input type="email" name="church_matrix_user" class="form-control" value="">
                                    </div>

                                    <div class="form-group">
                                        <label>Church Matrix API Key <span class="text-danger">*</span></label>
                                        <input type="text" name="church_matrix_api" class="form-control" value="">
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">Save API</button>
                                </div>
                            </div>
                        </form>

                        <!-- Static Location Form -->
                        {{-- <form action="#">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">Location ID</h5>
                                </div>
                                <div class="card-body">

                                    <div class="form-group">
                                        <label>Location ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="location_id"
                                            placeholder="Enter Location ID" value="">
                                    </div>

                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-success">Save Location</button>
                                </div>
                            </div>
                        </form> --}}

                    </div>

                    <div class="col-md-6 d-flex flex-column">

                        <div class="card shadow-sm flex-fill d-flex flex-column">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Select Region</h5>
                            </div>

                            <div class="card-body flex-fill">

                                <!-- Static message -->
                                <div class="alert alert-info small mb-3">
                                    Please save your Church Metrics API credentials first.
                                </div>

                                <form action="#">

                                    <div class="form-group">
                                        <label>Select Region <span class="text-danger">*</span></label>
                                        <select class="form-control" name="region_id" id="region_id" disabled>
                                            <option value="">-- Choose Region --</option>
                                        </select>
                                    </div>

                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-success" disabled>Save Region</button>
                            </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
