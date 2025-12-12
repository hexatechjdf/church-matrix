@extends('layouts.app')

@section('title', 'API Settings')

@section('content')

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

    @include('components.church-matrix-keys', ['admin' => true,'timezones' => $timezones])

    {{-- <div class="card shadow-sm mt-4">
        <div class="card-header text-white" style="background-color: #E9F0F0;">
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i>Own Church Matrix ApiKey</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive " id="tokenTableContainer">

            </div>
        </div>
    </div> --}}

@endsection
@push('script')

    <script>
        let page = '';
        $(document).ready(function(e) {
            page = '{{ route('church-matrix.request.listing') }}';
            fetchTokens(page);
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();

            page = $(this).attr('href'); // full URL mil jayega
            fetchTokens(page);
        });

        function fetchTokens(url = null) {
            url = url ?? page;
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    $("#tokenTableContainer").html(data);
                },
                error: function() {
                    toastr.error("Failed to load data");
                }
            });
        }
    </script>

    @include('components.submit-form')

@endpush
