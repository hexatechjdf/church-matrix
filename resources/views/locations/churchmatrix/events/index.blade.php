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
                <i class="me-3"></i>All Events
            </h4>
        </div>

        <div class="card-body p-0">

            <div id="eventsTableContainer">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold text-dark">Event Name</th>
                            <th class="fw-bold text-dark">Synced On</th>
                            <th class="text-center fw-bold text-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <!-- <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $event->name }}</td>
                            <td>{{ $event->created_at->format('d M Y') }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editEvent({{ $event->id }}, '{{ addslashes($event->name) }}')">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteEvent({{ $event->id }}, '{{ addslashes($event->name) }}')">Delete</button>
                            </td>
                        </tr> -->
                        <tr class="border-start border-4 border-primary" data-event-id="{{ $event->id }}" style="transition: all 0.3s;">
                            <td>
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0 fw-bold">{{ $event->name }}</h6>
                                </div>
                            </td>
                            <td class="text-muted"><i class="fas fa-calendar me-1"></i>{{ $event->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm rounded-circle shadow-sm me-2"
                                    onclick="editEvent({{ $event->id }}, '{{ addslashes($event->name) }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm  rounded-circle shadow-sm"
                                    onclick="deleteEvent({{ $event->id }}, '{{ addslashes($event->name) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No events found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


        </div>
    </div>
</div>


@include('locations.churchmatrix.events.add')

@include('locations.churchmatrix.events.edit')

@include('locations.churchmatrix.events.delete')

<script>
    function openAddModal() {
        $('#eventModal').modal('show');
        $('#modalTitle').html('<i class="fas fa-plus-circle me-3"></i>Add New Event');
        $('#saveBtnText').text('Save Event');

        $('#eventForm')[0].reset();
        $('#eventForm').attr('action', '{{ route("locations.churchmatrix.events.store") }}');


        $('#eventForm').find('input[name="_method"]').val('POST');
    }

    function editEvent(id, name) {
        $('#editEventModal').modal('show');
        $('#modalTitle').html('<i class="fas fa-edit me-3"></i>Edit Event');
        $('#saveBtnText').text('Update Event');
        $('#eventName').val(name);

        let updateUrl = "{{ route('locations.churchmatrix.events.update', ':id') }}";
        updateUrl = updateUrl.replace(':id', id);

        $('#editEventForm').attr('action', updateUrl);
        $('#editEventForm').find('input[name="_method"]').val('PUT');
    }

    function deleteEvent(id, name) {
    $('#deleteEventName').text(name);
    $('#deleteEventId').val(id);
    let deleteUrl = "{{ route('locations.churchmatrix.events.destroy', ':id') }}";
    deleteUrl = deleteUrl.replace(':id', id);
    $('#deleteForm').attr('action', deleteUrl);
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
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn:hover {
        transform: translateY(-3px);
        transition: 0.3s ease;
    }
</style>