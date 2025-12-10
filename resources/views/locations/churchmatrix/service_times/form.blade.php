<form id="addServiceTimeForm" action="{{ route('locations.churchmatrix.integration.service-times.manage') }}"
    method="POST" class="form-submit" data-table="serviceTimesTable">
    @csrf
    <input type="hidden" name="service_time_id" id="service_time_id" value="{{ @$id }}">
    <input type="hidden" id="modal_mode" value="{{ $mode }}">

    <div class="modal-header">
        <h5 class="modal-title" id="serviceModalTitle">Service Time</h5>
        <button type="button" class="btn-close  btn btn-danger btn-sm" data-bs-dismiss="modal">x</button>
    </div>

    <div class="modal-body">

        <div class="mb-3">
            <label>Campus</label>
            <select name="campus_id" class="form-control seelct2">
                <option value="">-- Select Campus --</option>
                @foreach (@$campuses ?? [] as $c)
                    @php($selected = $c['id'] == @$payload['campus_id'] ? 'selected' : '')
                    <option {{ $selected }} value="{{ $c['id'] ?? '' }}">{{ @$c['name'] ?? 'N/A' }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Day of Week</label>
            <select name="day_of_week" class="form-control" required>
                @foreach (days() as $k => $d)
                    @php($selected = $k == @$payload['day_of_week'] ? 'selected' : '')
                    <option {{ $selected }} value="{{ $k }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Time</label>
            <input type="time" name="time_of_day" value="{{ customDateTime(@$payload['time_of_day']) }}"
                class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Event (Optional)</label>
            <select name="event_id" class="form-control">
                <option value="">-- Select Event --</option>
                @foreach (@$events ?? [] as $event)
                    @php($selected = $event['id'] == @$payload['event_id'] ? 'selected' : '')
                    <option {{ $selected }} value="{{ $event['id'] ?? '' }}">{{ $event['name'] ?? 'N/A' }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Start Date</label>
            <input type="date" name="date_start" class="form-control" value="{{ @$payload['start_date'] }}"
                required>
        </div>

        <div class="mb-3">
            <label>End Date</label>
            <input type="date" name="date_end" class="form-control" value="{{ @$payload['end_date'] }}" required>
        </div>




    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>
            <span id="serviceModalBtnText">Add Service Time</span>
        </button>
    </div>

</form>
