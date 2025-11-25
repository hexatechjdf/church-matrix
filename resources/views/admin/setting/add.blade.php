@extends('layouts.app')

@section('title', 'Setting')

@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="">Setting</a></li>
                        {{-- <li class="breadcrumb-item active">Add New</li> --}}
                    </ol>
                </div>
                <h4 class="page-title">Add Setting</h4>
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

                    <form action="{{ route('setting.save') }}" method="POST" class="card-box">
                        @csrf
                        <span><strong>Note-</strong>These Two Fields are For Planning Center Connection.</span>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="client id">Client ID </label>
                                <input type="text" class="form-control @error('planning_client_id') is-invalid @enderror"
                                    name="planning_client_id" id="name" autocomplete="off">
                                @error('planning_client_id')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="secret_id">Client Secret ID</label>
                                <input type="text" class="form-control @error('planning_client_sceret') is-invalid @enderror"
                                    name="planning_client_sceret" id="secret_id" autocomplete="off">
                                @error('planning_client_sceret')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        {{-- FOR Planning Center --}}
                        <span><strong>Note-</strong>These Two Fields are For CRM Connection.</span>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="secret_id">Client ID</label>
                                <input type="text" class="form-control @error('crm_client_id') is-invalid @enderror"
                                    name="crm_client_id" id="crm_client_id" autocomplete="off">
                                @error('crm_client_id')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="secret_id">Client Secret ID</label>
                                <input type="text" class="form-control @error('crm_client_secret') is-invalid @enderror"
                                    name="crm_client_secret" id="crm_client_secret" autocomplete="off">
                                @error('crm_client_secret')
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
