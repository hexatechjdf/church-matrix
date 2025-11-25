@extends('layouts.app')
@section('title', 'Dashboard')
@section('css')

@endsection
@section('content')
    {{-- breadcrumb --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <div class="float-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
                <h4 class="page-title">Dashboard</h4>

                <div class="col-md-12 text-left mt-4">
             
                    @if(auth()->user()->role==1)
                 
                    <a href="https://api.planningcenteronline.com/oauth/authorize?client_id={{ getAccessToken('planning_client_id') }}&redirect_uri={{ route('planningcenter.callback') }}&response_type=code&scope=people"
                        class="btn btn-gradient-success px-4 mt-0 mb-3 import-users"><i
                            class="mdi mdi-plus-circle-outline mr-2"></i>Connect Planning Center</a>


                    <a href="https://marketplace.gohighlevel.com/oauth/chooselocation?response_type=code&redirect_uri={{ route('crm.callback') }}&client_id={{ getAccessToken('crm_client_id') }}&scope=calendars.readonly campaigns.readonly contacts.write contacts.readonly locations.readonly calendars/events.readonly locations/customFields.readonly locations/customValues.write opportunities.readonly calendars/events.write opportunities.write users.readonly users.write locations/customFields.write" class="btn btn-gradient-primary px-4 mt-0 mb-3"><i
                            class="mdi mdi-plus-circle-outline mr-2"></i>Connect CRM</a>
                </div>
                     
                <span class="ml-3"><strong>Note-</strong>Before Procedding kindly Connect With these CRM.</span><br>
            @endif
            </div>
            <!--end page-title-box-->
        </div>
        <!--end col-->
    </div>
    <!--end row-->
    <!-- end page title end breadcrumb -->
@endsection
