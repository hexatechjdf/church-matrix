@extends('layouts.location')

@section('title', 'Planning Center')
@push('style')
    <style>
        #loadingg {
            width: 100%;
            height: 100%;
            top: 0px;
            left: 0px;
            position: fixed;
            display: block;
            z-index: 99;
        }

        #loading-image {
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            z-index: 99999999999999;

        }

        #remove-overlay {
            background: rgb(0, 0, 0);
            opacity: 0.5;
            filter: alpha(opacity=50);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999;

        }


        .tdd {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
        }


        .disb {
            background-color: grey;

        }
    </style>
@endpush
@section('content')
    <div id="loadingg" class="loadingg d-none">
        <img id="loading-image" src="{{ asset('load.svg') }}" alt="Loading..." />
    </div>
    <div id="remove-overlay" class="loading-overlay"></div>

    @include('locations.components.topbar')
    <div class="p-4 bg-white">
        <div class="row">
            <div class="col-md-4 py-2">
                <div class="card">
                    <div class="card-body">
                        <img src="https://storage.googleapis.com/msgsndr/NP4dT88lEnnjb3WVmyAQ/media/640b02ecd29c8caf3f6233b4.png"
                            style="height:100px; width:100%; object-fit: contain;">
                        <a data-href="#" class="btn  form-control connect text-white   mt-0 mt-1 planningcenterbtn"
                            style='background-color:#E75037'><i class="mdi mdi-plus-circle-outline mr-2"></i>Connect</a>
                        <div class="planning_center_data d-none">
                            <div class="form-group">
                                <label>Choose Planning Center Workflow </label>
                                <select class="form-control select2 workflowsselected"
                                    onchange="return workflowchanged(this.value)">
                                </select>
                                <span>(Automatically add new Church Funnels contacts to this Planning Center
                                    workflow)</span>
                            </div>
                        </div>
                        <a data-href="#" class="btn form-control planningcenterbtn text-white  disconnect d-none mt-0 mt-1"
                            onclick="return disconnectplanning(this)" style='background-color:#E75037'>Disconnect - <span
                                id="organization_id"></span></a>
                        <a class="btn form-control text-white mt-3 mt-1"
                            href="https://churchfunnels.com/planning-center-integration" target="_blank"
                            style='background-color:#1c5be1'>How to Connect - Help Docs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $('.loadingg').removeClass('d-none');
            $('.loading-overlay').removeClass('d-none');

            checkForauth({
                location: '{{ request()->locationid }}',
                token: '{{ request()->sessionKey }}'
            });
        });


        function checkForauth(dt) {
            var url = "{{ route('locations.planningcenter.get.settings') }}";
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {

                    toastr.success("Fetched successfully!");
                    mainusertoken = data.token;
                    userjwt = data.jwt;


                    $('.planningcenterbtn.connect').attr('data-href', data.planning_href);

                    if (data.is_planning) {
                        hideshowplanning(data);
                        getWorkflows();
                    }
                },
                error: function(data) {
                    toastr.error("Something Wrong!");
                },
                complete: function() {
                    $('.loadingg').addClass('d-none');
                    $('.loading-overlay').addClass('d-none');
                }
            });
        }

        var childframe = null;

        function connect(url) {
            childframe = window.open(url, 'childframe', 'width=500,height=500');
            childframe.addEventListener("message", (e) => {
                var data = e.data;
                if (typeof data == 'string' && data == 'planningconnected') {
                    toastr.success("Planning Center Connected successfully!");
                    childframe.close();
                    hideshowplanning();
                    getWorkflows();
                    // window.location.href = "/";
                }
            });
        }

        function getWorkflows() {
            var url = "{{ route('auth.listworkflows') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {

                    token: userjwt
                },
                success: function(data) {
                    hideshowplanning(data);
                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                    $('.loadingg').addClass('d-none');
                    $('.loading-overlay').addClass('d-none');
                }
            });
        }

        $('body').on('click', '.planningcenterbtn.connect', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disb')) {
                return false;
            }
            connect($(this).attr('data-href'));
        });

        let mainusertoken = '';
        let userjwt = '';

        function workflowchanged(value) {
            var url = "{{ route('auth.saveWorkflow') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    workflow_id: value,
                    token: userjwt
                },
                success: function(data) {


                    console.log(data);

                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                    $('.loadingg').addClass('d-none');
                    $('.loading-overlay').addClass('d-none');
                }
            });
        }


        function disconnectplanning(e) {

            var url = "{{ route('auth.disconnectplanning') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {

                    token: userjwt
                },
                success: function(data) {

                    $('.planningcenterbtn.connect').removeClass('disb');
                    $('.planningcenterbtn.connect').show();
                    $('.planningcenterbtn.disconnect').addClass('d-none');
                    console.log(data);
                    $('.planning_center_data').addClass('d-none');

                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                    $('.loadingg').addClass('d-none');
                    $('.loading-overlay').addClass('d-none');
                }
            });
        }

        function hideshowplanning(data) {

            $('.planningcenterbtn.connect').addClass('disb');
            $('.planningcenterbtn.connect').hide();
            if (data?.organization_id) {
                $('#organization_id').html(data.organization_id ?? '');

                if (data?.organization_name) {

                    $('#organization_id').html(data.organization_name ?? '');
                }


            }

            $('.planningcenterbtn.disconnect').removeClass('d-none');
            if (data?.workflows?.data) {
                $('.planning_center_data').removeClass('d-none');
                $('.workflowsselected').html('<option value="">Select Workflow</option>');
                if (data.workflows.data.length == 0) {
                    //  $('.planning_center_data').addClass('d-none');
                }
                data.workflows.data.forEach(item => {
                    var selected = '';
                    if (data?.workflow_selected && item.id == data.workflow_selected) {
                        selected = 'selected="selected"';
                    }
                    $('.workflowsselected').append(
                        `<option value="${item.id}" ${selected}>${item.attributes.name}</option>`);
                });
            }
        }
    </script>
@endpush
