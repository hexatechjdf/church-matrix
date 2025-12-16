@php($user = loginUser())
<form id="filterForm">
    <div class="mb-3 field-date-range">
        <label class="fw-bold mb-2">Select Date Range</label>
        <input type="text" id="daterange" class="form-control " />
    </div>
    <div class="category_field_area field-category">
        @include('locations.churchmatrix.components.categoryfield')
    </div>

    @if ($user->church_admin)
        @include('locations.churchmatrix.components.campusfields')
    @endif

    <div class="mb-3 field-year">
        <label for="yearSelect" class="form-label">Year</label>
        <select id="yearSelect" name="year" class="form-select select2">
            <option value="">Select Year</option>
            @foreach (array_reverse(getYears() ?? []) as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3 field-month">
        <label for="monthSelect" class="form-label">Month(s)</label>
        <select id="monthSelect" name="months[]" class="form-select select2" multiple>
            @foreach (getMonths() ?? [] as $index => $month)
                <option value="{{ $index + 1 }}">{{ $month }}</option>
            @endforeach
        </select>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary w-100 mt-3">Apply Filters</button>
</form>
