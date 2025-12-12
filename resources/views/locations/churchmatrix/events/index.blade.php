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
                <h1 class="h3 mb-0">Church Events</h1>
                <p class="text-muted mb-0">Manage all events synced from Church Metrics</p>
            </div>
            <div> @include('button.index') </div>
        </div>


        @include('locations.components.modulegrid', ['active' => 'events','campuses' => false])

        <div class="">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4" data-toggle="modal"
                    data-target="#eventModal" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i>Add New Event
                </button>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
                style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

                <div class="card-header bg-gradient-primary text-white border-0 py-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="me-3"></i>All Events
                    </h4>
                </div>

                <div class="card-body p-0">

                    <div id="eventsTableContainer" class="p-3">
                        <table id="eventsTable" class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="fw-bold text-dark">Event Name</th>
                                    <th class="fw-bold text-dark">Synced On</th>
                                    <th class="text-center fw-bold text-dark">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="eventModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="eventForm" method="POST" data-table="eventsTable"
                    action="{{ route('locations.churchmatrix.integration.events.manage') }}" class="form-submit">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Event</h5>
                        <button type="button" class="btn-close btn btn-danger btn-sm" data-bs-dismiss="modal">x</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="event_id">
                        <div class="mb-3">
                            <label for="event_name" class="form-label">Event Name</label>
                            <input type="text" class="form-control" name="name" id="event_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
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

            $('#eventsTable').DataTable({
                processing: true,
                serverSide: false, // All data in one request
                ajax: "{{ route('locations.churchmatrix.integration.events.data') }}",
                pageLength: 20, // ✅ Sirf 1 row per page
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            let d = new Date(data);
                            return d.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: "text-center",
                        render: function(data) {
                            return `
                        <button class="btn btn-sm rounded-circle shadow-sm me-2"
                            onclick="editEvent(${data.id}, '${data.name}')">
                            <i class="fas fa-edit"></i>
                        </button>
                         <button class="btn btn-sm rounded-circle shadow-sm action-btn"
            data-url="/locations/churchmatrix/integration/events/destroy/${data.id}"
            data-message="You want to delete '${data.name}'?"
            data-success="Event deleted successfully!"
            data-table="eventsTable"
            data-function="reloadEvents">
            <i class="fas fa-trash"></i>
        </button>
                    `;
                        }
                    }
                ]
            });


            window.openAddModal = function() {
                $('#event_id').val('');
                $('#event_name').val('');
                $('#modalTitle').text('Add Event');
                $('#saveBtn').text('Save');
                $('#eventModal').modal('show');
            }

            // Open Edit Modal
            window.editEvent = function(id, name) {
                $('#event_id').val(id);
                $('#event_name').val(name);
                $('#modalTitle').text('Edit Event');
                $('#saveBtn').text('Update');
                $('#eventModal').modal('show');
            }

        });
    </script>
@endpush
