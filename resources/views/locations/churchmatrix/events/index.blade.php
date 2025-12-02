<div class="container-fluid px-4 py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-dark fw-bold">
                <i class="text-primary me-3"></i>Church Events
            </h2>
            <p class="text-muted mb-0">Manage all events synced from Church Metrics</p>
        </div>

        <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4"
                data-toggle="modal"
                data-target="#eventModal"
                onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Add New Event
        </button>
    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
         style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

        <div class="card-header bg-gradient-primary text-white border-0 py-4">
            <h4 class="mb-0 fw-bold">
                <i class="me-3"></i>All Events ({{ $events->count() }})
            </h4>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 fw-bold text-dark">#</th>
                            <th class="fw-bold text-dark">Event Name</th>
                            <th class="fw-bold text-dark">Synced On</th>
                            <th class="text-center fw-bold text-dark">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($events as $event)
                            <tr class="border-start border-4 border-primary" style="transition: all 0.3s;">
                                <td class="ps-4 fw-bold text-primary">{{ $loop->iteration }}</td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        

                                        <h6 class="mb-0 fw-bold">{{ $event->name }}</h6>
                                    </div>
                                </td>

                                <td class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $event->created_at->format('d M Y') }}
                                </td>

                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2"
                                            onclick="editEvent({{ $event->id }}, '{{ addslashes($event->name) }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger rounded-circle shadow-sm"
                                            onclick="deleteEvent({{ $event->id }}, '{{ addslashes($event->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div>
                                        <i class="fas fa-calendar-times fa-5x text-muted mb-4 opacity-50"></i>
                                        <h4 class="text-muted fw-light">No Events Found</h4>
                                        <p class="text-muted">Events will appear once synced from Church Metrics</p>

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


@include('events.add')


@include('events.delete')

<script>
function openAddModal() {
    $('#eventModal').modal('show');
    $('#modalTitle').html('<i class="fas fa-plus-circle me-3"></i>Add New Event');
    $('#saveBtnText').text('Save Event');

    $('#eventForm')[0].reset();
    $('#eventForm').attr('action', '{{ route('events.store') }}');

    $('#eventForm').find('input[name="_method"]').val('POST');
}

function editEvent(id, name) {
    $('#eventModal').modal('show');

    $('#modalTitle').html('<i class="fas fa-edit me-3"></i>Edit Event');
    $('#saveBtnText').text('Update Event');

    $('#eventName').val(name);

    $('#eventForm').attr('action', '/events/' + id);
    $('#eventForm').find('input[name="_method"]').val('PUT');
}

function deleteEvent(id, name) {
    $('#deleteEventName').text(name);
    $('#deleteForm').attr('action', '/events/' + id);
    $('#deleteModal').modal('show');
}
</script>



    <style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}
.bg-primary.bg-soft {
    background-color: rgba(102, 126, 234, 0.1) !important;
}
.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}
tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.btn:hover {
    transform: translateY(-3px);
    transition: 0.3s ease;
}
</style>
