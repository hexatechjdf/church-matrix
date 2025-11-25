@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Webhook URLS</li>
                    </ol>
                </div>
                <h4 class="page-title">Webhook URL</h4>
            </div>
            <!--end page-title-box-->
        </div>
        <!--end col-->
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        {{-- <div class="col-md-12 text-right">
            <a href="{{ route('user.import') }}" class="btn btn-gradient-success px-4 mt-0 mb-3 import-users"><i
                    class="mdi mdi-plus-circle-outline mr-2"></i>Import Users</a>
            <a href="{{ route('user.add') }}" class="btn btn-gradient-primary px-4 mt-0 mb-3"><i
                    class="mdi mdi-plus-circle-outline mr-2"></i>Add New</a>
        </div> --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3>WEBHOOK URLS</h3>
                    <div class="form-group row mt-4">
                        <div class="col-md-12">
                            <label for="first_name">Lead Capture WebHook *</label>
                            <input type="text" readonly placeholder="Lead Capture WebHook" class="form-control data-url copythis"
                                name="lead_capture_webhook" value="{{route('save_webhokk_data_to_dials', $id)}}" id="name" autocomplete="off">
                            <button class="btn btn-primary mt-1 copyUrl" type="button" id="button-addon2"
                               ><i class="far fa-copy mr-2"></i>Copy</button><br>

                            <span>Note-This WebHook will Capture the lead.You have to put this in your CallTools</span>

                        </div>

                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="first_name">Order Form WebHook *</label>
                            <input type="text" readonly placeholder="Order Form WebHook" class="form-control data-url copythis"
                                name="order_form_webhok" value="{{route('saveLocation', $id)}}" data-url="test" id="myInput" autocomplete="off">


                            <button class="btn btn-primary mt-1 copyUrl"  type="button"
                                id="copyUrl"><i class="far fa-copy mr-2"></i>Copy</button><br>
                            <span>Note- You have put this in your order-form submission</span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')

    <script>
      
        $('body').on('click', '.copyUrl', function() {
                navigator.clipboard.writeText($(this).closest('.form-group').find('.copythis').val()).then(function() {
                    toastr.success("Url Copied Successfully !");
                }, function() {
                    toastr.success("Can not copy , Something went wrong!");
                });
            })
    </script>

@endsection



{{-- <div class="input-group mb-2">
    <input type="text" class="form-control" id="projectUser"
        value="{{ route('webhook.action', ['user', 'account']) }}" aria-label="User Creation"
        aria-describedby="button-addon2" readonly>
    <button class="btn btn-primary  " type="button" id="button-addon2" onclick="copyText('#projectUser')"><i
            class="far fa-copy mr-2"></i>Copy</button>
</div> --}}
