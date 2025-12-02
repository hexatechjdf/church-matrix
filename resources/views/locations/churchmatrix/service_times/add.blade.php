<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Add Service Time</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form action="{{ route('events.store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="form-group">
                        <label>Campus ID</label>
                        <input type="text" name="campus_id" class="form-control" required placeholder="Enter campus ID">
                    </div>

                    <div class="form-group">
                        <label>Day of Week</label>
                        <input type="text" name="day_of_week" class="form-control" required placeholder="Enter day of week">
                    </div>

                    <div class="form-group">
                        <label>Time of Day</label>
                        <input type="time" name="time_of_day" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Timezone</label>
                        <input type="text" name="timezone" class="form-control" required placeholder="Enter timezone">
                    </div>

                    <div class="form-group">
                        <label>Relation to Sunday</label>
                        <select name="relation_to_sunday" class="form-control" required>
            
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date Start</label>
                        <input type="date" name="date_start" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Date End</label>
                        <input type="date" name="date_end" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Replaces</label>
                        <input type="text" name="replaces" class="form-control" placeholder="Enter Replaces">
                    </div>

                    <div class="form-group">
                        <label>Event ID</label>
                        <input type="text" name="event_id" class="form-control" required placeholder="Enter event ID">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Service Time</button>
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
