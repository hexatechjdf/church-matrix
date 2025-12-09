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


        @include('locations.components.modulegrid', ['active' => 'times', 'campuses' => $campuses])

        <div class="">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4" data-toggle="modal"
                    data-target="#serviceTimeModal" onclick="openAddServiceTimeModal()">
                    <i class="fas fa-plus me-2"></i>Add New
                </button>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
                style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

                <div class="card-header bg-gradient-primary text-white border-0 py-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="me-3"></i>All Service Times
                    </h4>
                </div>

                <div class="card-body p-0">

                    <div id="eventsTableContainer" class="p-3">
                        <table class="table table-hover align-middle mb-0" id="serviceTimesTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Campus</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Timezone</th>
                                    <th>Relation</th>
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

    <div class="modal fade" id="serviceTimeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg">

                <form id="addServiceTimeForm"
                    action="{{ route('locations.churchmatrix.integration.service-times.manage') }}" method="POST" class="form-submit">
                    @csrf
                    <input type="hidden" name="campus_id" class="campus_id" value=""> <!-- Static campus -->
                    <input type="hidden" name="service_time_id" id="service_time_id">
                    <input type="hidden" id="modal_mode" value="create">

                    <div class="modal-header">
                        <h5 class="modal-title" id="serviceModalTitle">Service Time</h5>
                        <button type="button" class="btn-close  btn btn-danger btn-sm" data-bs-dismiss="modal">x</button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Day of Week</label>
                            <select name="day_of_week" class="form-control" required>
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Time</label>
                            <input type="time" name="time_of_day" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Start Date</label>
                            <input type="date" name="date_start" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>End Date</label>
                            <input type="date" name="date_end" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Event (Optional)</label>
                            <select name="event_id" class="form-control">
                                <option value="">-- Select Event --</option>
                                @foreach (@$events ?? [] as $event)
                                    <option value="{{ $event['id'] ?? '' }}">{{ $event['name'] ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <span id="serviceModalBtnText">Add Service Time</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>



@endsection

@push('script')
    @include('components.submit-form')
    <script>
        $(document).ready(function() {

            let currentPage = 1;
            const perPage = 2;

            function loadServiceTimes(page = 1) {

                const campusId = $('#campus_id').val() || null;

                $.ajax({
                    url: "{{ route('locations.churchmatrix.integration.service-times.data') }}",
                    type: 'GET',
                    data: {
                        campus_id: campusId,
                        page,
                        per_page: perPage
                    },

                    success: function(res) {

                        $("#custom-pagination").remove();

                        let tbody = $('#serviceTimesTable tbody');
                        tbody.empty();

                        if (res.data.length === 0) {
                            tbody.append(
                                '<tr><td colspan="11" class="text-center py-5">No service times found</td></tr>'
                            );
                            return;
                        }

                        res.data.forEach((time, idx) => {
                            tbody.append(`
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${time.campus || 'N/A'}</td>
                        <td>${time.day || 'N/A'}</td>
                        <td>${time.time || 'N/A'}</td>
                        <td>${time.timezone || 'N/A'}</td>
                        <td>${time.relation || 'N/A'}</td>
                        <td>${time.date_start || 'N/A'}</td>
                        <td>${time.date_end || 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2"
                                onclick='editServiceTime(${JSON.stringify(time)})'>
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-sm btn-danger rounded-circle shadow-sm action-btn"
                                data-url="/locations/churchmatrix/integration/service-times/destroy/${time.id}"
                                data-message="You want to delete '${time.event || 'Service Time'}'?"
                                data-success="Service Time deleted!"
                                data-function="loadServiceTimes">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                        });

                        let pagination = `
                <div id="custom-pagination" class="d-flex justify-content-between mt-3">
                    <button id="prevBtn" class="btn btn-sm btn-primary"
                        ${!res.prev ? 'disabled' : ''}
                        onclick="goToPage(${page - 1})">Prev</button>

                    <span>Page ${page}</span>

                    <button id="nextBtn" class="btn btn-sm btn-primary"
                        ${!res.next ? 'disabled' : ''}
                        onclick="goToPage(${page + 1})">Next</button>
                </div>
            `;

                        $('#serviceTimesTable').after(pagination);
                        currentPage = page;
                    }
                });
            }

            function goToPage(page) {
                $("#prevBtn, #nextBtn").prop("disabled", true).text("Loading...");
                loadServiceTimes(page);
            }

            window.loadServiceTimes = loadServiceTimes;
            window.goToPage = goToPage;

            $(document).ready(function() {
                loadServiceTimes(currentPage);
            });


            window.openAddServiceTimeModal = function() {
                $('#service_time_id').val('');
                $('#modal_mode').val('create');
                let i = $('#campus_id').val();
                $('.campus_id').val(i);
                $('select[name="day_of_week"]').val('0');
                $('input[name="time_of_day"]').val('');
                $('input[name="date_start"]').val('');
                $('input[name="date_end"]').val('');
                $('select[name="event_id"]').val('');

                $('#serviceModalTitle').text('Add Service Time');
                $('#serviceModalBtnText').text('Add Service Time');

                $('#serviceTimeModal').modal('show');
            }

            window.editServiceTime = function(data) {
                console.log(data);
                $('#modal_mode').val('edit');
                $('#service_time_id').val(data.id);
                let i = $('#campus_id').val();
                $('.campus_id').val(i);

                // Correct field names
                $('select[name="day_of_week"]').val(String(data.day)); // day instead of day_of_week

                // Convert time to proper format for input[type="time"]
                let timeValue = data.time && data.time !== 'N/A' ? new Date(data.time).toISOString().slice(11,
                    16) : '';
                $('input[name="time_of_day"]').val(timeValue);

                $('input[name="date_start"]').val(data.date_start !== 'N/A' ? data.date_start : '');
                $('input[name="date_end"]').val(data.date_end !== 'N/A' ? data.date_end : '');
                $('select[name="event_id"]').val(data.event_id ?? '');

                $('#serviceModalTitle').text('Edit Service Time');
                $('#serviceModalBtnText').text('Update Service Time');

                $('#serviceTimeModal').modal('show');
            }

        });
    </script>
@endpush
