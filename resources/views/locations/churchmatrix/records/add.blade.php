<!-- Add Record Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add Record</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="" method="POST">
                @csrf

                <div class="modal-body">

                    <div class="form-group">
                        <label>Category ID</label>
                        <input type="text" name="category_id" class="form-control" required placeholder="Enter category ID">
                    </div>

                    <div class="form-group">
                        <label>Campus ID</label>
                        <input type="text" name="campus_id" class="form-control" required placeholder="Enter campus ID">
                    </div>

                    <div class="form-group">
                        <label>Service Time ID</label>
                        <input type="text" name="service_time_id" class="form-control" required placeholder="Enter service time ID">
                    </div>

                    <div class="form-group">
                        <label>Service Date & Time</label>
                        <input type="datetime-local" name="service_date_time" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Service Timezone</label>
                        <input type="text" name="service_timezone" class="form-control" required placeholder="Enter timezone">
                    </div>

                    <div class="form-group">
                        <label>Value</label>
                        <input type="number" name="value" class="form-control" required placeholder="Enter value">
                    </div>

                    <div class="form-group">
                        <label>Replaces</label>
                        <input type="text" name="replaces" class="form-control" placeholder="Enter event ID it replaces (optional)">
                    </div>

                    <div class="form-group">
                        <label>Event ID</label>
                        <input type="text" name="event_id" class="form-control" required placeholder="Enter unique event ID">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
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
