<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-body text-center py-5">
                <i class="fas fa-trash-alt fa-4x text-danger mb-4"></i>

                <h4>Delete Event?</h4>

                <p class="text-muted mb-4">
                    "<strong id="deleteEventName"></strong>" will be deleted permanently.
                </p>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="deleteEventId" name="">


                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-5">Yes, Delete</button>
                </form>

            </div>

        </div>

    </div>
</div>


@push('js')
<script>
   

   $(document).on('submit', '#deleteForm', function(e) {
    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    formData.append('_token', $('input[name="_token"]').val());
    formData.append('_method', 'DELETE');

    let $submitBtn = $(form).find('button[type="submit"]');
    let originalText = $submitBtn.html();
    $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);

    $.ajax({
        url: form.action,
        method: 'POST', 
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.success) {
                $('#deleteModal').modal('hide');

                let row = document.querySelector(`tr[data-event-id="${$('#deleteForm').find('#deleteEventId').val()}"]`);
                if (row) row.remove();

                Toast.fire({
                    icon: 'success',
                    title: res.message || 'Event deleted successfully!'
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: res.message || 'Failed to delete event'
                });
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


</script>
@endpush