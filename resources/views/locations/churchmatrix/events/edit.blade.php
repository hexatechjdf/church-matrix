<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg glass-effect">
            <div class="modal-header border-0 py-4 px-4 modal-gradient">
                <h4 class="modal-title fw-bold d-flex align-items-center" id="modalTitle">
                    Edit Eventsssss
                </h4>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
            </div>

            <form id="editEventForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="event_id" id="edit_event_id">

                <div class="modal-body px-5 py-4">
                    <div class="form-group mb-4">
                        <label class="fw-bold text-dark">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_event_name"
                            class="form-control form-control-lg rounded-pill shadow-sm bg-light border-0" required>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 px-5">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 shadow-lg btn-animated">
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('js')
<script>
    function editEvent(id, name) {

        $('#editEventModal').modal('show');
        $('#edit_event_id').val(id);
        $('#edit_event_name').val(name);

        let url = "{{ route('locations.churchmatrix.events.update', ':id') }}".replace(':id', id);
        $('#editEventForm').attr('action', url);
    }

   $(document).on('submit', '#editEventForm', function(e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    // CSRF & method
    formData.append('_token', $('input[name="_token"]').val());
    formData.append('_method', 'PUT');

    let $submitBtn = $(form).find('button[type="submit"]');
    let originalText = $submitBtn.html();
    $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);

    $.ajax({
        url: form.action,
        method: 'POST', // Laravel spoofing
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.success) {
                // Hide modal
                $('#editEventModal').modal('hide');

                // Update the row in table
                let row = document.querySelector(`tr[data-event-id="${res.event.id}"]`);
                if (row) {
                    row.querySelector('h6').textContent = res.event.name;
                }

                // Toast
                Toast.fire({
                    icon: 'success',
                    title: res.message || 'Event updated successfully!'
                });

                // Reset form for next use (same as create)
                form.reset();
                $('#modalTitle').html('<i class="fas fa-calendar-plus me-3"></i> Add New Event');
                $('#saveBtnText').text('Save Event');
                form.action = '{{ route("locations.churchmatrix.events.store") }}';
                $(form).find('input[name="_method"]').val('POST');
            }
        },
        error: function(xhr) {
            let msg = xhr.responseJSON?.message || 'Something went wrong!';
            Toast.fire({ icon: 'error', title: msg });
        },
        complete: function() {
            $submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

$('#editEventModal').on('hidden.bs.modal', function() {
    $(this).find('form')[0].reset();
    $('#saveBtnText').text('Save Event');
    $('#modalTitle').html('<i class="fas fa-calendar-plus me-3"></i> Add New Event');
});

</script>
@endpush