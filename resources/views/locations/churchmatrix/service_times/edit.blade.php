<div class="modal fade" id="editServiceTimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">

            <form id="editServiceTimeForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="campus_id" id="edit_campus_id">

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
                                <option value="{{ $event->id }}">{{ $event->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> 
                        <span id="saveEditServiceTimeBtnText">Update Service Time</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('js')
<script>
    function editServiceTime(serviceTime) {
        $('#editServiceTimeModal').modal('show');

        // Campus ID (must have fallback)
        $('#edit_campus_id').val(serviceTime.campus_id || 137882);

        // Day of week
        $('#edit_day_of_week').val(serviceTime.day_of_week);

        // Time: API se "2025-12-04T10:30:00Z" aata hai → sirf "10:30" chahiye
        let timeValue = '';
        if (serviceTime.time_of_day) {
            timeValue = serviceTime.time_of_day.split('T')[1]?.substring(0, 5) || '';
        }
        $('#edit_time_of_day').val(timeValue);

        // Dates
        $('#edit_date_start').val(serviceTime.date_start || '');
        $('#edit_date_end').val(serviceTime.date_end || '');

        // Event
        $('#edit_event_id').val(serviceTime.event?.id || '');

        // SABSE JARURI: cm_id use karo, local id nahi!
        const cmId = serviceTime.cm_id;
        if (!cmId) {
            Toast.fire({ icon: 'error', title: 'ChurchMatrix ID missing!' });
            $('#editServiceTimeModal').modal('hide');
            return;
        }

        // Dynamic URL with cm_id
        let url = "{{ route('locations.churchmatrix.service-times.update', ':id') }}";
        url = url.replace(':id', cmId);
        $('#editServiceTimeForm').attr('action', url);
    }

    function updateServiceTimeRow(serviceTime) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const row = $(`tr[data-service-time-id="${serviceTime.id}"]`);

        // Time ko sirf HH:MM dikhao
        let displayTime = serviceTime.time_of_day || '';
        if (displayTime.includes('T')) {
            displayTime = displayTime.split('T')[1].substring(0, 5);
        }

        row.html(`
            <td class="ps-4 fw-bold text-primary">#</td>
            <td>${serviceTime.campus_id || 'N/A'}</td>
            <td>${days[serviceTime.day_of_week] || serviceTime.day_of_week}</td>
            <td>${displayTime}</td>
            <td>${serviceTime.timezone || 'N/A'}</td>
            <td>${serviceTime.relation_to_sunday || 'N/A'}</td>
            <td>${serviceTime.date_start || ''}</td>
            <td>${serviceTime.date_end || ''}</td>
            <td>${serviceTime.replaces ? 'Yes' : 'No'}</td>
            <td>${serviceTime.event?.name || 'N/A'}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2"
                        onclick='editServiceTime(${JSON.stringify(serviceTime)})'>
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger rounded-circle shadow-sm"
                        onclick="deleteServiceTime(${serviceTime.id}, '${(serviceTime.event?.name || '').replace(/'/g, "\\'")}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `);

        row.addClass('new-row-highlight');
        setTimeout(() => row.removeClass('new-row-highlight'), 3000);
    }

    $(document).on('submit', '#editServiceTimeForm', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);
        const $btn = $(form).find('button[type="submit"]');
        const originalText = $btn.html();

        $btn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);

        $.ajax({
            url: form.action,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#editServiceTimeModal').modal('hide');
                    updateServiceTimeRow(res.service_time);

                    Toast.fire({
                        icon: 'success',
                        title: res.message || 'Service Time updated successfully!'
                    });
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Update failed!';
                Toast.fire({ icon: 'error', title: msg });
            },
            complete: function() {
                $btn.html(originalText).prop('disabled', false);
            }
        });
    });

    $('#editServiceTimeModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('#saveEditServiceTimeBtnText').text('Update Service Time');
    });
</script>
@endpush