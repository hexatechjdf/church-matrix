<div class="modal fade" id="addServiceTimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg">

            <form id="addServiceTimeForm" action="{{ route('locations.churchmatrix.service-times.store') }}" method="POST">
                @csrf
                <!-- Static campus -->
                <input type="hidden" name="campus_id" value="137882">

                <div class="modal-header">
                    <h5 class="modal-title">Add Service Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Day of Week</label>
                        <select name="day_of_week" class="form-control" required>
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
                        <input type="time" name="time_of_day" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Start Date</label>
                        <input type="date" name="date_start" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>End Date</label>
                        <input type="date" name="date_end" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Event (Optional)</label>
                        <select name="event_id" class="form-control">
                            <option value="">-- Select Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id ?? '' }}">{{ $event->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> <span id="saveServiceTimeBtnText">Add Service Time</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('js')
<script>

    console.log('fdf');
    // const Toast = Swal.mixin({
    //     toast: true,
    //     position: 'top-end',
    //     showConfirmButton: false,
    //     timer: 3000,
    //     timerProgressBar: true,
    //     didOpen: (toast) => {
    //         toast.addEventListener('mouseenter', Swal.stopTimer)
    //         toast.addEventListener('mouseleave', Swal.resumeTimer)
    //     }
    // });

    function prependServiceTimeRow(serviceTime) {
        // Optionally remove temp row if using temporary IDs
        $(`tr[data-temp-id="${serviceTime.id}"]`).remove();

        const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        let newRow = `
        <tr class="border-start border-4 border-primary new-row-highlight" data-service-time-id="${serviceTime.id}">
            <td class="ps-4 fw-bold text-primary">#</td>
            <td>${serviceTime.campus_id}</td>
            <td>${days[serviceTime.day_of_week] ?? serviceTime.day_of_week}</td>
            <td>${serviceTime.time_of_day}</td>
            <td>${serviceTime.timezone ?? 'N/A'}</td>
            <td>${serviceTime.relation_to_sunday ?? 'N/A'}</td>
            <td>${serviceTime.date_start}</td>
            <td>${serviceTime.date_end}</td>
            <td>${serviceTime.replaces ? 'Yes' : 'No'}</td>
            <td>${serviceTime.event?.name ?? 'N/A'}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-warning rounded-circle shadow-sm me-2"
                        onclick='editServiceTime(${JSON.stringify(serviceTime)})'>
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger rounded-circle shadow-sm"
                        onclick="deleteServiceTime(${serviceTime.id}, '${serviceTime.event?.name ?? ''}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
        $('#serviceTimesTable tbody').prepend(newRow);
    }

    $(document).ready(function() {
        $('#addServiceTimeForm').submit(function(e) {
            e.preventDefault();
            let form = $(this);
            let $submitBtn = form.find('button[type="submit"]');
            let originalText = $submitBtn.html();
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(res) {

                    console.log(res)

                    if (res.success) {
                        $('#addServiceTimeModal').modal('hide');
                        form[0].reset();
                        Toast.fire({
                            icon: 'success',
                            title: res.message || 'Service Time added successfully!'
                        });
                         console.log('before')

                        prependServiceTimeRow(res.service_time);
                         console.log('after')

                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: res.message || 'Failed to add Service Time'
                        });
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Something went wrong!'
                    });
                },
                complete: function() {
                    $submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        $('#addServiceTimeModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#saveServiceTimeBtnText').text('Add Service Time');
            $('.modal-title').text('Add Service Time');
        });
    });
</script>
@endpush
