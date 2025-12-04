<div class="modal fade" id="editServiceTimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">

            <form id="editServiceTimeForm" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" id="edit_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="editServiceTimeTitle">Edit Service Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Day of Week</label>
                        <select name="day_of_week" id="edit_day_of_week" class="form-control" required>
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
                        <input type="time" name="time_of_day" id="edit_time_of_day" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" name="date_start" id="edit_date_start" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="date_end" id="edit_date_end" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Event (Optional)</label>
                        <select name="event_id" id="edit_event_id" class="form-control">
                            <option value="">-- Select Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Service Time
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

   $(document).on('submit', '#editServiceTimeForm', function(e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    formData.append('_token', $('input[name="_token"]').val());
    formData.append('_method', 'PUT');

    let $submitBtn = $(form).find('button[type="submit"]');
    let originalText = $submitBtn.html();
    $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);

    $.ajax({
        url: form.action,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        success: function(res) {
            if (res.success) {

                $('#editServiceTimeModal').modal('hide');

                let row = document.querySelector(`tr[data-service-time-id="${res.time.id}"]`);
                if (row) {
                    row.children[2].textContent = res.time.day_text;
                    row.children[3].textContent = res.time.time_formatted;
                    row.children[6].textContent = res.time.date_start;
                    row.children[7].textContent = res.time.date_end;
                    row.children[9].textContent = res.time.event_name ?? "N/A";
                }

                Toast.fire({
                    icon: 'success',
                    title: res.message || 'Service Time updated successfully!'
                });

                form.reset();
                form.action = "";
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


$('#editServiceTimeModal').on('hidden.bs.modal', function() {
    $(this).find('form')[0].reset();
});

</script>
@endpush