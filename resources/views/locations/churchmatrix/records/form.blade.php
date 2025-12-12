<input type="hidden" name="record_id" value="{{ @$id }}">



@if (!@$id)

    @if ($user->church_admin)
        @include('locations.churchmatrix.components.campusfields')
    @endif
    @include('locations.churchmatrix.components.eventfields')

    <div class="mb-3">
        <label class="form-label fw-bold">Service Time</label>
        <select name="service_time_id" class="form-control service-time-select" required></select>
    </div>
@endif



<table class="table table-borderless">
    <thead>
        <tr>
            <th width="40%">Attendance Type</th>
            <th width="60%">Value</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($categories as $cat)
            @php
                $parent = $cat['parent'];
                $children = $cat['children'];
                $showParent = true;

                if (isset($payload)) {
                    // Parent should show only if parent is selected OR any child matches
                    $showParent =
                        $parent['id'] == $selectedCategoryId ||
                        collect($children)->pluck('id')->contains($selectedCategoryId);
                }
            @endphp

            @if ($showParent)
                {{-- Parent row --}}
                <tr>
                    <td class="fw-bold">{{ $parent['name'] }}</td>
                    <td>
                        @if (count($children) === 0 && (!isset($payload) || $parent['id'] == $selectedCategoryId))
                            <input type="number" name="category_values[{{ $parent['id'] }}]" class="form-control"
                                min="0" placeholder="Enter value"
                                value="{{ isset($payload) && $parent['id'] == $selectedCategoryId ? $payload->value : '' }}">
                        @endif
                    </td>
                </tr>

                {{-- Child rows --}}
                @if (count($children) > 0)
                    @foreach ($children as $child)
                        @php
                            $showChild = !isset($payload) || $child['id'] == $selectedCategoryId;
                        @endphp

                        @if ($showChild)
                            <tr>
                                <td class="ps-4">— {{ $child['name'] }}</td>
                                <td>
                                    <input type="number" name="category_values[{{ $child['id'] }}]"
                                        class="form-control" min="0" placeholder="Enter value"
                                        value="{{ isset($payload) && $child['id'] == $selectedCategoryId ? $payload->value : '' }}">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            @endif
        @endforeach
    </tbody>
</table>




<div class="mb-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i>
        <span id="serviceModalBtnText">Submit</span>
    </button>
</div>
