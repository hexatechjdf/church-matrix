@extends('layouts.app')

@section('title', 'API Settings')

@section('content')

@php
$regions = session('regions') ?? $regions ?? null;
@endphp

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="float-right">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                </ol>
            </div>
            <h4 class="page-title">API Configuration</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="row">
            <div class="col-md-6">

                <form action="{{ route('church-matrix.save-api') }}" method="POST">
                    @csrf
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Church Matrix API</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>User Auth (Email) <span class="text-danger">*</span></label>
                                <input type="email" name="church_matrix_user" class="form-control"
                                    value="{{ $settings->church_matrix_user ?? '' }}" required>
                            </div>

                            <div class="form-group">
                                <label>Church Matrix API Key <span class="text-danger">*</span></label>
                                <input type="text" name="church_matrix_api" class="form-control"
                                    value="{{ $settings->church_matrix_api ?? '' }}" required>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Save API</button>
                        </div>
                    </div>
                </form>

                <form action="{{ route('church-matrix.save-location') }}" method="POST">
                    @csrf
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2"></i>Location ID</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="location_id">Location ID <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('location_id') is-invalid @enderror"
                                    name="location_id"
                                    id="location_id"
                                    placeholder="Enter Location ID"
                                    value="{{ $settings->location_id ?? '' }}">
                                @error('location_id')
                                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Save Location</button>
                        </div>
                    </div>
                </form>


            </div>

            <div class="col-md-6 d-flex flex-column">
                <div class="card shadow-sm flex-fill d-flex flex-column">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Select Region</h5>
                    </div>

                    <div class="card-body flex-fill">


                        @php
                        // Priority: session → controller → null
                        $displayRegions = session('regions') ?? $regions ?? null;
                        @endphp

                        @if($displayRegions)
                        <div class="alert alert-success small mb-3">
                            Connected! {{ count($displayRegions) }} region{{ count($displayRegions) != 1 ? 's' : '' }} loaded successfully.
                        </div>
                        @elseif($settings ?? null)
                        <div class="alert alert-warning small mb-3">
                            API credentials are saved, but regions could not be loaded. Please check your email or API key.
                        </div>
                        @else
                        <div class="alert alert-info small mb-3">
                            Please save your Church Metrics API credentials first.
                        </div>
                        @endif

                        <form action="{{ route('church-matrix.save-region') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="region_id">
                                    Select Region <span class="text-danger">*</span>
                                </label>

                                <select class="form-control"
                                    name="region_id"
                                    id="region_id"
                                    {{ $regions ? '' : 'disabled' }}>
                                    <option value="">-- Choose Region --</option>
                                    @if($regions)
                                    @foreach($regions as $region)
                                    <option value="{{ $region['id'] }}"
                                        {{ ($settings->select_region ?? '') == $region['id'] ? 'selected' : '' }}>
                                        {{ $region['name'] }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit"
                            class="btn btn-success px-4"
                            {{ $regions ? '' : 'disabled' }}>
                            Save Region
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header text-white" style="background-color: #E9F0F0;">
                <h5 class="mb-0"><i class="fas fa-table mr-2"></i>Saved API & Location Data</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>User Email</th>
                                <th>Location ID</th>
                                <th>API Key</th>
                                <th>Region</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allSettings as $key => $setting)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $setting->church_matrix_user }}</td>
                                <td>{{ $setting->location_id ?? '-' }}</td>
                                <td>{{ $setting->church_matrix_api }}</td>
                                <td>
                                    @php
                                    $regionName = '-';
                                    if(isset($setting->select_region) && $regions) {
                                    foreach($regions as $region) {
                                    if($region['id'] == $setting->select_region) {
                                    $regionName = $region['name'];
                                    break;  } }}
                                    @endphp
                                    {{ $regionName }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No data found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</div>
@endsection