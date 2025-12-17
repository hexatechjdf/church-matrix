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
                <h1 class="h3 mb-0">All Service Times</h1>
                <p class="text-muted mb-0">Manage all Service Times synced from Church Metrics</p>
            </div>
            <div> @include('button.index') </div>
        </div>


        @include('locations.components.modulegrid', ['active' => 'times', 'campuses' => false])

        <div class="">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4 get-times-form" data-toggle="modal"
                    data-target="#serviceTimeModal">
                    <i class="fas fa-plus me-2"></i>Add New
                </button>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
                style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

                <div class="card-header bg-gradient-primary text-white border-0 py-4 d-flex justify-content-between">
                    <h4 class="mb-0 fw-bold">
                        <i class="me-3"></i>All Service Times
                    </h4>
                    <button type="button" class="refresh-btn btn btn-warning" data-url="{{ route('locations.churchmatrix.integration.update.times') }}" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div class="card-body p-0">

                    <div id="eventsTableContainer" class="p-3">
                        <table class="table table-hover align-middle mb-0" id="serviceTimesTable">
                            <thead class="bg-light">
                                <tr>
                                    @if ($user->church_admin)
                                        <th>Campus</th>
                                    @endif
                                    <th>Time</th>
                                    <th>Timezone</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
                    <h5 class="modal-title" id="serviceModalTitle">Service Time</h5>
                    <button type="button" class="btn-close btn btn-danger btn-sm" data-dismiss="modal">x</button>
                </div>

                <form id="addServiceTimeForm"
                    action="{{ route('locations.churchmatrix.integration.service-times.manage') }}" method="POST"
                    class="form-submit" data-table="serviceTimesTable">
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
        $(document).ready(function() {

            let serverSideCall = `{{ !$user->church_admin }}`;

            $('#serviceTimesTable').DataTable({
                processing: true,
                serverSide: serverSideCall, // client-side pagination since all data is local
                ajax: "{{ route('locations.churchmatrix.integration.service-times.data') }}",
                pageLength: 10, // adjust as needed
                columns: [
                    @if ($user->church_admin)
                        {
                            data: 'campus_name',
                            name: 'campus_name'
                        },
                    @endif {
                        data: 'time_of_day',
                        name: 'time_of_day'
                    },
                    {
                        data: 'timezone',
                        name: 'timezone'
                    },
                    {
                        data: 'date_start',
                        name: 'date_start'
                    },
                    {
                        data: 'date_end',
                        name: 'date_end'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function(data) {
                            return `
                                 <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2 get-times-form"
                            data-obj='${JSON.stringify(data)}'>
                            <i class="fas fa-edit"></i>
                        </button>

                        <button class="btn btn-sm btn-danger rounded-circle shadow-sm action-btn"
                            data-url="/locations/churchmatrix/integration/service-times/destroy/${data.cm_id}"
                            data-message="You want to delete '${data.event || 'Service Time'}'?"
                            data-success="Service Time deleted!"
                            data-table="serviceTimesTable"
                            data-function="loadServiceTimes">
                            <i class="fas fa-trash"></i>
                        </button>
                `;
                        }
                    }
                ]
            });

            // $(document).on("click", ".get-times-form", function() {
            //     let id = $(this).data("id") || null;

            //     $.ajax({
            //         url: "{{ route('locations.churchmatrix.integration.records.form') }}",
            //         type: "GET",
            //         data: {
            //             id
            //         },
            //         beforeSend: function() {
            //             $("#formContainer").html("<p class='text-center'>Loading...</p>");
            //         },
            //         success: function(res) {
            //             $("#formContainer").html(res.html);

            //             loadTimes();
            //             $('.service-time-select').select2({
            //                 dropdownParent: $("#recordsModal"),
            //                 width: '100%',
            //                 placeholder: "Select Service Time",
            //                 allowClear: true,
            //             });

            //             // Apply select2 on event type
            //             $('.event-type-select').select2({
            //                 dropdownParent: $("#recordsModal"),
            //                 width: '100%',
            //                 placeholder: "Select Event Type",
            //                 allowClear: true
            //             });

            //             $('#recordsModal').modal('show');
            //         }
            //     });
            // });

            $(document).on("click", ".get-times-form", function() {
                let payload = $(this).data('obj') ?? '';
                $("#serviceTimeModal").modal("show");
                $.ajax({
                    url: "{{ route('locations.churchmatrix.integration.service-times.form') }}",
                    type: "GET",
                    data: {
                        payload
                    },

                    beforeSend: function() {
                        $("#serviceLoader").show();
                        $("#serviceTimeModalBody").html("");
                    },

                    success: function(res) {
                        $("#serviceTimeModalBody").html(res);

                        initSelect2("#fetchselect2", "events");
                        if (!serverSideCall) {
                            initSelect2("#fetchselect2", "campuses");

                            if (payload.campus_id) {
                                setSelect2Selected($('select[name="campus_id"]'), payload
                                    .campus_id, payload.campus_name);
                            }
                        }
                        if (payload.event_id) {
                            setSelect2Selected($('select[name="event_id"]'), payload.event_id,
                                payload.event_name);
                        }
                        toggleDateFields();
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




            // window.openAddServiceTimeModal = function() {
            //     let campusId = $('#campus_id').val();

            //     $.ajax({
            //         url: "{{ route('locations.churchmatrix.integration.service-times.form') }}",
            //         type: "GET",
            //         data: {
            //             mode: 'create',
            //         },
            //         success: function(html) {
            //             $('#serviceTimeModal .modal-content').html(html);

            //             $('input[name="date_start"], input[name="date_end"]').closest('.mb-3')
            //                 .hide();

            //             $('#serviceTimeModal').modal('show');
            //         }
            //     });
            // };

            // window.editServiceTime = function(data) {
            //     let campusId = $('#campus_id').val();

            //     $.ajax({
            //         url: "{{ route('locations.churchmatrix.integration.service-times.form') }}",
            //         type: "GET",
            //         data: {
            //             mode: 'edit',
            //             id: data.id,
            //             payload: data,
            //         },
            //         success: function(html) {
            //             $('#serviceTimeModal .modal-content').html(html);

            //             let eventId = $('select[name="event_id"]').val();
            //             if (eventId) {
            //                 $('input[name="date_start"], input[name="date_end"]').closest('.mb-3')
            //                     .show();
            //             } else {
            //                 $('input[name="date_start"], input[name="date_end"]').closest('.mb-3')
            //                     .hide();
            //             }

            //             $('#serviceTimeModal').modal('show');
            //         }
            //     });
            // };

            function toggleDateFields() {
                let val = $('select[name="event_id"]').val();

                if (val) {
                    $('input[name="date_start"], input[name="date_end"]').closest('.mb-3').show();
                } else {
                    $('input[name="date_start"], input[name="date_end"]').closest('.mb-3').hide();
                }
            }

            // Run toggle when select2 changes
            $(document).on('change', 'select[name="event_id"]', function() {
                toggleDateFields();
            });

        });
    </script>
@endpush
