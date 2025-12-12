<input type="hidden" name="service_time_id" id="service_time_id" value="{{ @$id }}">

@if ($user->church_admin)
    @include('locations.churchmatrix.components.campusfields')
@endif

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
    <input type="time" name="time_of_day" value="{{ customDateTime(@$payload['time_of_day']) }}" class="form-control"
        required>
</div>

@include('locations.churchmatrix.components.eventfields')

<div class="mb-3">
    <label>Start Date</label>
    <input type="date" name="date_start" class="form-control" value="{{ @$payload['date_start'] }}">
</div>

<div class="mb-3">
    <label>End Date</label>
    <input type="date" name="date_end" class="form-control" value="{{ @$payload['date_end'] }}">
</div>

<div class="mb-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>
        <span id="serviceModalBtnText">Submit</span>
    </button>
</div>
