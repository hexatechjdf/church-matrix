<input type="hidden" name="record_id" value="{{ @$id }}">
{{-- EVENTS --}}
<div class="mb-3">
    <label class="form-label fw-bold">Event</label>
    <select name="event_id" class="form-control" required>
        <option value="">-- Select Event --</option>
        @foreach ($events as $ev)
            <option value="{{ $ev['id'] ?? '' }}">{{ $ev['name'] ?? 'No Name' }}</option>
        @endforeach
    </select>
</div>

{{-- CAMPUS --}}
<div class="mb-3">
    <label class="form-label fw-bold">Campus</label>
    <select name="campus_id" class="form-control" required>
        <option value="">-- Select Campus --</option>
        @foreach ($campuses as $campus)
            <option value="{{ $campus['id'] ?? '' }}">{{ $campus['name'] ?? 'No Name' }}</option>
        @endforeach
    </select>
</div>

{{-- SERVICE TIMES --}}
<div class="mb-3">
    <label class="form-label fw-bold">Service Time</label>
    <select name="service_time_id" class="form-control service-time-select" required></select>
</div>


<hr>

{{-- CATEGORIES DISPLAY --}}
<table class="table table-borderless">
    <thead>
        <tr>
            <th width="40%">Category</th>
            <th width="60%">Value</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($categories as $cat)
            @php
                $parent = $cat['parent'];
                $children = $cat['children'];
            @endphp

            {{-- PARENT ROW --}}
            <tr>
                <td class="fw-bold">{{ $parent['name'] }}</td>

                {{-- Parent has children → NO INPUT --}}
                <td>
                    @if (count($children) === 0)
                        <input type="number" name="category_values[{{ $parent['id'] }}]" class="form-control"
                            min="0" placeholder="Enter value">
                    @else
                        <span class="text-muted">Has subcategories</span>
                    @endif
                </td>
            </tr>

            {{-- CHILDREN ROWS --}}
            @if (count($children) > 0)
                @foreach ($children as $child)
                    <tr>
                        <td class="ps-4">— {{ $child['name'] }}</td>
                        <td>
                            <input type="number" name="category_values[{{ $child['id'] }}]" class="form-control"
                                min="0" placeholder="Enter value">
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>
