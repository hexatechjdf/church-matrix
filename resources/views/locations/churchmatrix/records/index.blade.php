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
            <h1 class="h3 mb-0">Church Records</h1>
            <p class="text-muted mb-0">Manage all records synced from Church Metrics</p>
        </div>
        <div> @include('button.index') </div>
    </div>

    @include('locations.components.modulegrid', ['active' => 'events','campuses' => $campuses])

    <div class="">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4" data-toggle="modal"
                data-target="#recordsModal" onclick="openAddModal()">
                <i class="fas fa-plus me-2"></i>Add New Record
            </button>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
            style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

            <div class="card-header bg-gradient-primary text-white border-0 py-4">
                <h4 class="mb-0 fw-bold">
                    <i class="me-3"></i>All Records
                </h4>
            </div>

            <div class="card-body p-0">

                <div id="eventsTableContainer" class="p-3">
                    <table class="table table-hover align-middle mb-0" id="records-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Record ID</th>
                                <th>Week</th>
                                <th>Service Date</th>
                                <th>Service Time</th>
                                <th>Value</th>
                                <th>Campus ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>

                </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="recordsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="recordsForm" method="POST" action="#" class="form-submit">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Record</h5>
                    <button type="button" class="btn-close btn btn-danger btn-sm" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">

                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th width="30%">Category</th>
                                        <th width="70%">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $index => $cat)
                                    <tr>
                                        <td class="align-middle">
                                            <strong>{{ $cat['name'] ?? 'Category ' }}</strong>
                                        </td>
                                        <td>
                                            @foreach($cat['fields'] ?? [] as $field)
                                            @if(($field['type'] ?? '') === 'readonly')
                                            <input type="text" class="form-control mb-2" value="{{ $field['value'] ?? '' }}" readonly>

                                            @elseif(($field['type'] ?? '') === 'select')
                                            <select class="form-control mb-2" name="select_fields[{{ $index }}][]">
                                                <option value="">-- Select --</option>
                                                @foreach($field['options'] ?? [] as $opt)
                                                <option value="{{ $opt }}" {{ ($field['value'] ?? '') == $opt ? 'selected' : '' }}>
                                                    {{ $opt }}
                                                </option>
                                                @endforeach
                                            </select>

                                            @elseif(($field['type'] ?? '') === 'custom')
                                            <input type="text"
                                                name="custom_fields[{{ $index }}][]"
                                                class="form-control mb-2"
                                                value="{{ $field['value'] ?? '' }}"
                                                placeholder="Enter details">
                                            @else

                                            <input type="text"
                                                name="custom_fields[{{ $index }}][]"
                                                class="form-control mb-2"
                                                placeholder="Enter value">
                                            @endif
                                            @endforeach

                                            @if(empty($cat['fields']))
                                            <input type="text"
                                                name="custom_fields[{{ $index }}][]"
                                                class="form-control mb-2"
                                                placeholder="Enter value">
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Service Time <span class="text-danger">*</span></label>
                                    <select name="service_time" class="form-control" required>
                                        <option value="">-- Select Duration --</option>
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60">1 Hour</option>
                                        <option value="90">1 Hour 30 Min</option>
                                        <option value="120">2 Hours</option>
                                        <option value="180">3 Hours</option>
                                        <option value="240">4 Hours</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Event Type <span class="text-danger">*</span></label>
                                    <select name="event_type" class="form-control" required>
                                        <option value="">-- Select Event --</option>
                                        @foreach($events ?? [] as $event)
                                        <option value="{{ is_object($event) ? $event->id : ($event['id'] ?? '') }}">
                                            {{ is_object($event) ? $event->name : ($event['name'] ?? 'No Name') }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>


@endsection

@push('script')
@include('components.submit-form')
<script>
$(function() {
    $('#records-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url()->current() }}",
        columns: [
            { data: 'record_unique_id', name: 'record_unique_id' },
            { data: 'week_reference',   name: 'week_reference' },
            { data: 'service_date_time',name: 'service_date_time' },
            { data: 'service_time',     name: 'service_time' },
            { data: 'value',            name: 'value' },
            { data: 'campus_unique_id', name: 'campus_unique_id' },
            {
                data: 'id',
                title: 'Actions',
                orderable: false,
                searchable: false,
                className: 'text-center',
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
});
</script>
@endpush
