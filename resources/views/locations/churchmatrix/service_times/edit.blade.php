<!-- Edit Service Time Modal -->
<div class="modal fade" id="editServiceTimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">

            <form id="editServiceTimeForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="service_time_id" id="edit_service_time_id">
                <input type="hidden" name="campus_id" value="137882">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Service Time</h5>
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
                        <input type="date" name="date_start" id="edit_date_start" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="date_end" id="edit_date_end" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Include in Weekly Reports? (replaces regular service)</label>
                        <select name="replaces" id="edit_replaces" class="form-control">
                            <option value="0">No (extra event)</option>
                            <option value="1">Yes (e.g. Easter, Christmas)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Event (Optional)</label>
                        <select name="event_id" id="edit_event_id" class="form-control">
                            <option value="">-- No Event --</option>
                            @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> <span id="saveEditBtnText">Update Service Time</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


@push('js')
<script>
    console.log('before ready 1st');


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

                    let row = document.querySelector(`tr[data-service-time-id="${res.service_time.id}"]`);
                    if (row) {
                        row.outerHTML = createServiceTimeRow(res.service_time);
                    }

                    Toast.fire({
                        icon: 'success',
                        title: res.message || 'Service time updated successfully!'
                    });

                    form.reset();
                    $('#editServiceTimeForm').attr('action', '');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Something went wrong!';
                Toast.fire({
                    icon: 'error',
                    title: msg
                });
            },
            complete: function() {
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });



    function formatTime(t) {
    if (!t) return '';
    const [h, m] = t.split(':');
    const hour = ((+h + 11) % 12 + 1);
    const ampm = +h >= 12 ? 'PM' : 'AM';
    return `${hour}:${m} ${ampm}`;
}

function createServiceTimeRow(st) {
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const time = formatTime(st.time_of_day);
    const eventName = st.event?.name || '';
    const replacesText = st.replaces ? 'Yes' : 'No';

    return `
<tr class="border-start border-4 border-primary" data-service-time-id="${st.id}">
    <td class="ps-4 fw-bold text-primary">#</td>
    <td>${st.campus_id}</td>
    <td>${days[st.day_of_week]}</td>
    <td>${time}</td>
    <td>${st.timezone || 'N/A'}</td>
    <td>${st.relation_to_sunday || 'N/A'}</td>
    <td>${st.date_start || '-'}</td>
    <td>${st.date_end || '-'}</td>
    <td>${replacesText}</td>
    <td>${eventName}</td>
    <td class="text-center">
        <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2"
            onclick='editServiceTime(${JSON.stringify(st)})'>
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-sm btn-danger rounded-circle shadow-sm"
            onclick="deleteServiceTime(${st.id}, '${eventName.replace(/'/g, "\\'")}')">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>`;
}

</script>
@endpush