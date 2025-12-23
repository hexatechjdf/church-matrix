@extends('layouts.location')

@section('title', 'Settings')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/setting_integration.css') }}">
    <style>
        .settings-container {
            max-width: 1300px;
        }

        .settings-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .module-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .module-card.active {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .stats-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #10b981;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .icon-wrapper.events {
            background: #ede9fe;
        }

        .icon-wrapper.service {
            background: #fce7f3;
        }

        .icon-wrapper.records {
            background: #dbeafe;
        }

        .module-icon {
            font-size: 1.75rem;
        }

        .icon-wrapper.events .module-icon {
            color: #8b5cf6;
        }

        .icon-wrapper.service .module-icon {
            color: #ec4899;
        }

        .icon-wrapper.records .module-icon {
            color: #3b82f6;
        }

        .module-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .module-desc {
            color: #6b7280;
            font-size: 0.875rem;
            margin: 0;
        }

        .module-arrow {
            position: absolute;
            bottom: 1.5rem;
            right: 1.5rem;
            color: #9ca3af;
        }

        .module-content-area {
            display: none;
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .module-content-area.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')

    <div class="settings-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0">Church Attendance Records</h1>
                <p class="text-muted mb-0">Manage all records synced from Church Metrics</p>
            </div>
            <div> @include('button.index') </div>
        </div>

        @include('locations.components.modulegrid', ['active' => 'records', 'campuses' => false])

        <div class="">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4 get-records-form">
                    <i class="fas fa-plus me-2"></i>Add New Attendance
                </button>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
                style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

                <div class="card-header bg-gradient-primary text-white border-0 py-4 d-flex justify-content-between">
                    <h4 class="mb-0 fw-bold">
                        <i class="me-3"></i>All Attendances
                    </h4>

                    <div class="d-flex ">
                        <button type="button" class="refresh-btn btn btn-warning mr-3"
                            data-url="{{ route('locations.churchmatrix.integration.update.records') }}" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        {{-- <a class=" btn btn-primary" href="{{ route('locations.churchmatrix.integration.stats.index') }}"
                            title="Refresh">
                            <i class="fas fa-chart-line"></i> Statistics
                        </a> --}}
                    </div>
                </div>

                <div class="card-body p-0">

                    <div id="eventsTableContainer" class="p-3">
                        <table class="table table-hover align-middle mb-0" id="records-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Record ID</th>
                                    <th>Service Date</th>
                                    <th>Value</th>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Category</th>
                                    @if ($user->church_admin)
                                    <th>Campus</th>
                                    @endif
                                    <th>Event</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceTimeModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalTitle">Attendance Records</h5>
                    <button type="button" class="btn-close btn btn-danger btn-sm" data-dismiss="modal">x</button>
                </div>

                <form id="addServiceTimeForm" action="{{ route('locations.churchmatrix.integration.records.manage') }}"
                    method="POST" class="form-submitt" data-table="serviceTimesTable">
                    @csrf

                    <div class="modal-body" id="fetchselect2">
                        <div id="serviceLoader" class="text-center py-4" style="display:none;">
                            <div class="spinner-border"></div>
                            <p class="mt-2">Loading...</p>
                        </div>

                        <div id="serviceTimeModalBody"></div>

                    </div>

                </form>
            </div>
        </div>
    </div>


@endsection

@push('script')
    @include('components.submit-form')
    @include('locations.churchmatrix.components.script')
    <script>
        let allServiceTimes = [];
        let serverSideCall = `{{ !$user->church_admin }}`;
        $(function() {
            $('#records-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url()->current() }}",
                columns: [{
                        data: 'record_unique_id',
                        name: 'record_unique_id'
                    },
                    {
                        data: 'service_date_time',
                        name: 'service_date_time'
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'year',
                        name: 'year'
                    },
                    {
                        data: 'week_no',
                        name: 'week_no'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    @if ($user->church_admin)
                        {
                            data: 'campus_name',
                            name: 'campus_name'
                        },
                    @endif
                    {
                        data: 'event_name',
                        name: 'event_name'
                    },
                    {
                        data: 'id',
                        title: 'Actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `

                         <button class="btn btn-sm rounded-circle shadow-sm me-2 get-records-form"
                            data-id="${row.record_unique_id}">
                            <i class="fas fa-edit"></i>
                        </button>

                           <button class="btn btn-sm rounded-circle shadow-sm action-btn"
            data-url="/locations/churchmatrix/integration/records/destroy/${row.record_unique_id}"
            data-message="You want to delete?"
            data-success="Event deleted successfully!"
            data-table="records-table">
            <i class="fas fa-trash"></i>
        </button>
                    `;
                        }
                    }
                ]
            });


            $(document).on("click", ".get-records-form", function() {
                let id = $(this).data('id') ?? '';
                $("#serviceTimeModal").modal("show");
                $.ajax({
                    url: "{{ route('locations.churchmatrix.integration.records.form') }}",
                    type: "GET",
                    data: {
                        id: id
                    },

                    beforeSend: function() {
                        $("#serviceLoader").show();
                        $("#serviceTimeModalBody").html("");
                    },

                    success: function(res) {
                        $("#serviceTimeModalBody").html(res);

                        initSelect2("#fetchselect2", "events");
                        initSelect2("#fetchselect2", "service-time");
                        if (!serverSideCall) {
                            initSelect2("#fetchselect2", "campuses");
                        }
                    },

                    error: function(xhr) {
                        $("#serviceTimeModalBody").html(
                            "<p class='text-danger'>Error loading form. Please try again.</p>"
                        );
                    },

                    complete: function() {
                        $("#serviceLoader").hide();

                    }
                });
            });



            $(document).on('submit', '#addServiceTimeForm', function(e) {
                e.preventDefault();

                let $form = $(this);
                let $btn = $form.find('button[type="submit"]');

                $btn.prop('disabled', true).text('Submitting...');

                let record_id = $form.find('input[name="record_id"]').val();
                let campus_id = $form.find('select[name="campus_id"]').val();
                let event_id = $form.find('select[name="event_id"]').val();
                let service_time_id = $form.find('select[name="service_time_id"]').val();

                let inputs = $form.find('input[name^="category_values"]').filter(function() {
                    return $(this).val() !== "";
                });

                let totalRequests = inputs.length;
                let completedRequests = 0;

                // Agar koi input hi nahi, to reload hi kar do
                if (totalRequests === 0) {
                    $btn.prop('disabled', false).text('Submit');
                    return;
                }

                inputs.each(function() {

                    let $input = $(this);
                    let value = $input.val();
                    let category_id = $input.attr('name').match(/\d+/)[0];

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: {
                            _token: $('input[name="_token"]').val(),
                            record_id,
                            campus_id,
                            event_id,
                            service_time_id,
                            category_id,
                            value
                        },

                        success: function(res) {

                            $input.removeClass('is-invalid is-valid');
                            $input.next('.invalid-feedback').remove();

                            if (res.success) {
                                $input.addClass('is-valid');
                            } else if (res.errors) {
                                let msg = "";

                                Object.keys(res.errors).forEach(function(key) {
                                    msg += res.errors[key][0] + "<br>";
                                });

                                $input.addClass('is-invalid');

                                if ($input.next('.invalid-feedback').length === 0) {
                                    $input.after(
                                        `<div class="invalid-feedback">${msg}</div>`
                                    );
                                } else {
                                    $input.next('.invalid-feedback').html(msg);
                                }
                            }
                        },

                        error: function(xhr) {
                            let msg = "Server error. Try again.";

                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors)[0][0];
                            }

                            $input.addClass('is-invalid');

                            if ($input.next('.invalid-feedback').length === 0) {
                                $input.after(
                                    `<div class="invalid-feedback">${msg}</div>`);
                            }
                        },

                        complete: function() {

                            completedRequests++;

                            // 🔥 Jab saari AJAX complete ho jayein → tab reload kro
                            if (completedRequests === totalRequests) {
                                $btn.prop('disabled', false).text('Submit');

                                $('#records-table').DataTable().ajax.reload(null,
                                    false);
                            }
                        }
                    });
                });
            });







        });
    </script>
@endpush
