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
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
                <h4 class="page-title">Users</h4>
            </div>
            <!--end page-title-box-->
        </div>
        <!--end col-->
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 text-right">
            {{-- <a href="{{ route('user.import') }}" class="btn btn-gradient-success px-4 mt-0 mb-3 import-users"><i
                    class="mdi mdi-plus-circle-outline mr-2"></i>Import Users</a> --}}
            <a href="{{ route('user.add') }}" class="btn btn-gradient-primary px-4 mt-0 mb-3"><i
                    class="mdi mdi-plus-circle-outline mr-2"></i>Add New</a>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable" class="table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                                <!--end tr-->
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Datatable
        let table = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{ route('user.list') }}",
            },
            columns: [{
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },


                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                },

                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    class: 'text-right'
                },
            ]
        });


        $('body').on('click', '.import-users', function(e) {
            e.preventDefault();
            $('.loading').show();
            let url = $(this).attr('href');
            // ajax call
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('.loading').hide();
                    if (data.status == 'success') {
                        toastr.success(data.message);
                        table.draw();
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function(data) {
                    $('.loading').hide();
                    toastr.error(data.message);
                }
            });
        });
    </script>


    {{-- <script>
        $('body').on('click', '.copyUrl', function() {
            navigator.clipboard.writeText($(this).attr('data-url')).then(function() {
                toastr.success("Url Copied Successfully !");
            }, function() {
                toastr.success("Can not copy , Something went wrong!");
            });
        })
    </script> --}}
@endsection
