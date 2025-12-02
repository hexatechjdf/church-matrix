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

                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-5">Yes, Delete</button>
                </form>

            </div>

        </div>

    </div>
</div>
