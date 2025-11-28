<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add Event</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="{{ route('events.store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="form-group">
                        <label>Event Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter event name">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save Event</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    function openEventModal() {
        $('#addEventModal').modal('show');
    }
</script>
