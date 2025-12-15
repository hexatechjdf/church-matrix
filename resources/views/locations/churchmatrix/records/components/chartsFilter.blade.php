@php($user = loginUser())
<form id="filterForm">
    <div class="category_field_area">
        @include('locations.churchmatrix.components.categoryfield')
    </div>

    @if ($user->church_admin)
        @include('locations.churchmatrix.components.campusfields')
    @endif

    <div class="mb-3">
        <label for="yearSelect" class="form-label">Year</label>
        <select id="yearSelect" name="year" class="form-select select2">
            <option value="">Select Year</option>
            @foreach (array_reverse(getYears() ?? []) as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <!-- Month Select (Multiple) -->
    <div class="mb-3">
        <label for="monthSelect" class="form-label">Month(s)</label>
        <select id="monthSelect" name="months[]" class="form-select select2" multiple>
            @foreach (getMonths() ?? [] as $index => $month)
                <option value="{{ $index + 1 }}">{{ $month }}</option>
            @endforeach
        </select>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
</form>
