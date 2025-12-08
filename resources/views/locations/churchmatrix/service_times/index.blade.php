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
                        <tr class="border-start border-4 border-primary"
                            data-service-time-id="{{ $time->id }}"
                            data-cm-id="{{ $time->cm_id ?? '' }}">
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
                                <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2"
                                    onclick='editServiceTime(@json($time))'>
                                    <i class="fas fa-edit"></i>
                                </button>


                                <button type="button"
                                    class="btn btn-sm btn-danger rounded-circle shadow-sm"
                                    onclick="deleteServiceTime({{ $time->id }}, '{{ addslashes($time->event?->name ?? 'Service Time') }}')">
                                    <i class="fas fa-trash"></i> </button>
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


<!-- Delete Modal (Yeh bilkul yeh hi use karo) -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <i class="fas fa-trash-alt fa-4x text-danger mb-4"></i>
                <h4>Delete Service Time?</h4>
                <p class="text-muted">
                    "<strong id="deleteEventName" class="text-danger"></strong>"
                    will be deleted <strong class="text-danger">permanently</strong>.
                </p>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="deleteUrl" value="">
                    <div class="mt-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-5">Yes, Delete</button>
                    </div>
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
        // Populate modal fields
        $('#edit_service_time_id').val(serviceTime.id);
        $('#edit_day_of_week').val(serviceTime.day_of_week);

        // Extract HH:MM from ISO datetime string
        $('#edit_time_of_day').val(serviceTime.time_of_day ? serviceTime.time_of_day.substring(11, 16) : '');

        $('#edit_date_start').val(serviceTime.date_start || '');
        $('#edit_date_end').val(serviceTime.date_end || '');
        $('#edit_replaces').val(serviceTime.replaces ? 1 : 0);
        $('#edit_event_id').val(serviceTime.event?.id || '');

        // Set the form action dynamically
        const updateUrl = `{{ route('locations.churchmatrix.service-times.update', ':id') }}`.replace(':id', serviceTime.id);
        $('#editServiceTimeForm').attr('action', updateUrl);

        // Show modal
        $('#editServiceTimeModal').modal('show');
    }

    window.deleteServiceTime = function(id, name) {
        // Name set karo
        document.getElementById('deleteEventName').textContent = name || 'Service Time';

        // Form ka action set karo
        document.getElementById('deleteForm').action = '/service-times/' + id;

        // Modal kholo — 3 tarike se (ek bhi ho to chalega)
        try {
            $('#deleteModal').modal('show'); // jQuery way
        } catch (e) {}

        try {
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show(); // Bootstrap 5 pure JS
        } catch (e) {}

        try {
            document.getElementById('deleteModal').classList.add('show');
            document.getElementById('deleteModal').style.display = 'block';
        } catch (e) {}
    };

    // Delete form submit
    document.getElementById('deleteForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = this.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Deleting...';

        const url = this.action;

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: '_method=DELETE'
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Row remove karo
                    document.querySelector(`tr[data-service-time-id="${id}"]`)?.remove();

                    // Success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Deleted successfully!');
                    } else {
                        alert('Deleted!');
                    }

                    // Modal band karo
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                    if (modal) modal.hide();
                }
            })
            .catch(() => {
                alert('Delete failed!');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = original;
            });
    });
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