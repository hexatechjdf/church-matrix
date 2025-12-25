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

    @include('components.church-matrix-keys', ['admin' => true, 'timezones' => $timezones])

    <div class="card shadow-sm mt-4">

        <div class="card-header d-flex justify-content-between align-items-center mb-3">
            <h4>Campuses</h4>
            <button class="btn btn-success open-modal" id="">
                + Add Campus
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="campusTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Campus ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="campusModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <form id="campusForm" method="POST" action="{{ route('campuses.save') }}" class="form-submit"
                data-table="campusTable">
                @csrf
                <div class="modal-content" id="fetchselect2">
                    <input type="hidden" name="campus_name" id="campus_name">
                    <input type="hidden" name="id" id="campus_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Create / Edit Campus</h5>
                        <button type="button" class="btn-close btn btn-danger" data-bs-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">
                        @include('locations.churchmatrix.components.campusfields')
                        <div class="mb-2">
                            <label>Location ID</label>
                            <input type="text" name="location_id" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('script')
    @include('locations.churchmatrix.components.script')
    <script>
        $(function() {

            // Datatable
            let table = $('#campusTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('campuses.list') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'campus_unique_id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'location_id'
                    }, {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function(data) {
                            return `
                                <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2 open-modal"
                                    data-mode="edit"
                                    data-obj='${JSON.stringify(data)}'>
                                    <i class="fas fa-edit"></i>
                                </button>
                            `;
                        }
                    }
                ]
            });

            function openCampusModal(mode = 'create', payload = {}) {
                $('#campusForm')[0].reset();
                $('#campus_id').val('');
                $('#campusModal').modal('show');

                $('#campus_id').val('');

                initSelect2("#fetchselect2", "campuses");
                if (mode === 'edit' && payload) {
                    $('#campus_id').val(payload.id ?? '');
                    Object.keys(payload).forEach(function(key) {
                        let $field = $('[name="' + key + '"]');

                        if ($field.length) {
                            $field.val(payload[key]);
                        }
                    });

                    if (payload.campus_unique_id) {
                        $('#campus_name').val(payload.name);
                        $('#campus_id').val(payload.id);
                        setSelect2Selected($('select[name="campus_id"]'), payload.campus_unique_id,
                            payload.name);
                    }
                }
            }


            $(document).on('click', '.open-modal', function() {
                let payload = $(this).data('obj') ?? {};
                openCampusModal('edit', payload);
            });

            $('.campus-select').on('select2:select', function(e) {
                let data = e.params.data;
                $('#campus_name').val(data.text); // campus name
            });



        });
    </script>
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
