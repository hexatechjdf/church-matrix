@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.list') }}">User</a></li>
                        <li class="breadcrumb-item active">Edit User</li>

                    </ol>
                </div>
                <h4 class="page-title">Edit User</h4>
            </div>
            <!--end page-title-box-->
        </div>
        <!--end col-->
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('user.save', $data->id) }}" method="POST" class="card-box">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="name">Name *</label>
                                <input type="text" placeholder="Name"
                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ old('name', $data->name) }}" id="name" autocomplete="off">
                                @error('name')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="email">Email *</label>
                                <input type="text" placeholder="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email', $data->email) }}" id="email" autocomplete="off">
                                @error('email')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="api_key">CRM API Key * <strong class="ml-3"> ( Note: </strong><small
                                        class="text-danger">Must be an Agency Key, as it required to hit the users api calls
                                    </small>)</label>
                                <textarea placeholder="Api Key" class="form-control @error('api_key') is-invalid @enderror" name="api_key">{{ $data->ghl_api_key ?? '' }}</textarea>
                                @error('api_key')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <div class="col-md-12">
                                <label for="location"> Location Id * </label>
                                <textarea placeholder="Location Id " class="form-control @error('location') is-invalid @enderror"
                                    name="location">{{ $data->location ?? '' }}</textarea>
                                @error('location')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="api_key">Password *</label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Password">
                                </div>
                                @error('password')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <a href="{{ route('user.list') }}"
                                class="btn btn-danger btn-sm text-light px-4 mt-3 float-right mb-0 ml-2">Cancel</a>
                            <button type="submit"
                                class="btn btn-primary btn-sm text-light px-4 mt-3 float-right mb-0">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
