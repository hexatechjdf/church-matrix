<div class="modules-grid">

    <div class="module-card active" data-module="events" data-url="{{ route('locations.churchmatrix.events.index') }}">
        <div class="icon-wrapper events">
            <i class="fas fa-calendar-check module-icon"></i>
        </div>
        <div class="module-content">
            <h3 class="module-title">Events</h3>
            <p class="module-desc">Create, manage, and track all church events and activities</p>
        </div>
        <i class="fas fa-chevron-right module-arrow"></i>
    </div>

    <div class="module-card" data-module="service-times"
        data-url="{{ route('locations.churchmatrix.service-times.index') }}">
        <div class="icon-wrapper service">
            <i class="fas fa-clock module-icon"></i>
        </div>
        <div class="module-content">
            <h3 class="module-title">Service Time</h3>
            <p class="module-desc">Schedule and organize service timings for your congregation</p>
        </div>
        <i class="fas fa-chevron-right module-arrow"></i>
    </div>

    <div class="module-card" data-module="records" data-url="{{ route('locations.churchmatrix.records.index') }}">
        <div class="icon-wrapper records">
            <i class="fas fa-database module-icon"></i>
        </div>
        <div class="module-content">
            <h3 class="module-title">Records</h3>
            <p class="module-desc">View, manage, and organize all church records securely</p>
        </div>
        <i class="fas fa-chevron-right module-arrow"></i>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <label for="campus_id">Campus</label>
        <select name="campus_id" id="campus_id" class="form-control select2">
            @foreach ($campuses as $campus)
                <option value="{{ $campus['id'] }}">
                    {{ $campus['name'] }}
                </option>
            @endforeach
        </select>
    </div>
</div>

@push('script')
    <script>
        $(document).on('click', '.module-card', function() {
            var module = $(this).data('module');
            var url = $(this).data('url');

            window.location.href = url;
        });
    </script>
@endpush
