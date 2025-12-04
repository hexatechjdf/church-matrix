<div class="container-fluid px-4 py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-dark fw-bold">
                <i class="text-primary me-3"></i>Service Times
            </h2>
            <p class="text-muted mb-0">Manage all service times linked to events</p>
        </div>

        <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4"
            onclick="openAddServiceTimeModal()">
            <i class="fas fa-plus me-2"></i>Add Service Time
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 fw-bold text-dark">#</th>
                            <th class="fw-bold text-dark">Campus</th>
                            <th class="fw-bold text-dark">Day</th>
                            <th class="fw-bold text-dark">Time</th>
                            <th class="fw-bold text-dark">Timezone</th>
                            <th class="fw-bold text-dark">Relation</th>
                            <th class="fw-bold text-dark">Start Date</th>
                            <th class="fw-bold text-dark">End Date</th>
                            <th class="fw-bold text-dark">Replaces</th>
                            <th class="fw-bold text-dark">Event</th>
                            <th class="text-center fw-bold text-dark">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="serviceTimesTable">
                        @php
                        $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                        @endphp

                        @forelse($serviceTimes as $time)
                        @php
                        $time = (object) $time;
                        $time->event = isset($time->event) ? (object) $time->event : null;
                        $time->campus = isset($time->campus) ? (object) $time->campus : null;
                        @endphp
                        <tr class="border-start border-4 border-primary" data-service-time-id="{{ $time->id }}">
                            <td class="ps-4 fw-bold text-primary">{{ $loop->iteration }}</td>
                            <td>{{ $time->campus->slug ?? $time->campus_id ?? 'N/A' }}</td>
                            <td>{{ $days[$time->day_of_week] ?? $time->day_of_week }}</td>
                            <td>{{ \Carbon\Carbon::parse($time->time_of_day)->format('h:i A') }}</td>
                            <td>{{ $time->timezone ?? 'N/A' }}</td>
                            <td>{{ $time->relation_to_sunday ?? 'N/A' }}</td>
                            <td>{{ $time->date_start ?? 'N/A' }}</td>
                            <td>{{ $time->date_end ?? 'N/A' }}</td>
                            <td>{{ isset($time->replaces) ? ($time->replaces ? 'Yes' : 'No') : 'N/A' }}</td>
                            <td>{{ $time->event->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm rounded-circle shadow-sm me-2"
                                    onclick="editServiceTime({{ $time->id }}, '{{ addslashes($time->event->name ?? 'Service Time') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm rounded-circle shadow-sm"
                                    onclick="deleteServiceTime({{ $time->id }}, '{{ addslashes($time->event->name ?? 'Service Time') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="noServiceTimesRow">
                            <td colspan="11" class="text-center py-5">
                                <div>
                                    <i class="fas fa-clock fa-5x text-muted mb-4 opacity-50"></i>
                                    <h4 class="text-muted fw-light">No Service Times Found</h4>
                                    <p class="text-muted">Add service times linked to events</p>
                                    <button class="btn btn-outline-primary px-4" onclick="location.reload()">
                                        <i class="fas fa-sync-alt me-2"></i>Refresh
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>



<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center py-5">
                <i class="fas fa-trash-alt fa-4x text-danger mb-4"></i>
                <h4>Delete Service Time?</h4>
                <p class="text-muted mb-4">"<strong id="deleteEventName"></strong>" will be deleted permanently.</p>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-5">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('locations.churchmatrix.service_times.add')
@include('locations.churchmatrix.service_times.edit')

<script>
    function openAddServiceTimeModal() {
        $('#addServiceTimeModal').modal('show');
        $('#modalTitle').html('<i class="fas fa-plus-circle me-3"></i>Add Service Time');
        $('#saveBtnText').text('Save Service Time');

        $('#serviceTimeForm')[0].reset();

    }

function editServiceTime(serviceTime) {
    $('#editServiceTimeModal').modal('show');

    $('#edit_id').val(serviceTime.id);
    $('#edit_campus_id').val(serviceTime.campus_id || 137882); 
    $('#edit_day_of_week').val(serviceTime.day_of_week);
    $('#edit_time_of_day').val(serviceTime.time_of_day);
    $('#edit_date_start').val(serviceTime.date_start);
    $('#edit_date_end').val(serviceTime.date_end);
    $('#edit_event_id').val(serviceTime.event?.id || '');

    let url = "{{ route('locations.churchmatrix.service-times.update', ':id') }}";
    url = url.replace(':id', serviceTime.cm_id || serviceTime.id);
    $('#editServiceTimeForm').attr('action', url);
}




    function deleteServiceTime(id, name) {
        $('#deleteEventName').text(name);
        $('#deleteForm').attr('action', '/service-times/' + id);
        $('#deleteModal').modal('show');
    }
</script>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        transform: translateY(-3px);
        transition: 0.3s ease;
    }
</style>