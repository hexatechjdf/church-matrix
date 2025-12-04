<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg glass-effect">

            <div class="modal-header border-0 py-4 px-4 modal-gradient">
                <h4 class="modal-title fw-bold d-flex align-items-center" id="modalTitle">
                    <i class="fas fa-calendar-plus me-3"></i>
                    Add New Event
                </h4>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
            </div>

            <form id="eventForm" method="POST">
                @csrf
                @method('POST')

                <div class="modal-body px-5 py-4">

                    <div class="form-group mb-4">
                        <label class="fw-bold text-dark">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="eventName"
                            class="form-control form-control-lg rounded-pill shadow-sm bg-light border-0"
                            placeholder="Enter Event"
                            required>
                    </div>

                </div>
                <div class="modal-footer border-0 pb-4 px-5">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 shadow-sm" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-lg btn-animated">
                        <i class="fas fa-save me-2"></i>
                        <span id="saveBtnText">Save Event</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>



@push('js')
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    function prependEventRow(event) {
        $(`tr[data-temp-id="${event.id}"]`).remove();

        const escapedName = event.name.replace(/'/g, "\\'").replace(/"/g, "\\\"");

        let newRow = `
        <tr class="border-start border-4 border-primary new-row-highlight" data-event-id="${event.id}">
      
            <td>
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-bold">${event.name}</h6>
                </div>
            </td>
            <td class="text-muted">
                <i class="fas fa-calendar me-1"></i> Just Now
            </td>
            <td class="text-center">
                <button class="btn btn-sm rounded-circle shadow-sm me-2"
                        onclick="editEvent(${event.id}, '${escapedName}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm rounded-circle shadow-sm"
                        onclick="deleteEvent(${event.id}, '${escapedName}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#eventsTableContainer tbody').prepend(newRow);
    }

    $(document).ready(function() {

        $('#eventForm').submit(function(e) {
            e.preventDefault();

            let form = $(this);
            let url = form.attr('action');
            let data = form.serialize();
            let $submitBtn = form.find('button[type="submit"]');
            let originalText = $submitBtn.html();
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

            $.ajax({
                type: form.find('input[name="_method"]').val() || 'POST',
                url: url,
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#eventModal').modal('hide');
                        form[0].reset();
                        Toast.fire({
                            icon: 'success',
                            title: response.message || 'Event created successfully!'
                        });
                        prependEventRow(response.event);

                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Failed to create event'
                        });
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

        $('#eventModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#saveBtnText').text('Save Event');
            $('#modalTitle').html('<i class="fas fa-calendar-plus me-3"></i> Add New Event');
        });
    });
</script>
@endpush