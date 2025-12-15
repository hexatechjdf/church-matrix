<div class="row mb-3">
    <!-- Year Filter -->
    <div class="col-md-3">
        <label class="form-label">Year</label>
        <select id="yearFilter" class="form-select">
            <option value="">Current Year</option>
            @for ($y = now()->year; $y >= 2020; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>
    </div>

    <!-- Month Filter (Multiple) -->
    <div class="col-md-5">
        <label class="form-label">Months</label>
        <select id="monthFilter" class="form-select" multiple>
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
        <small class="text-muted">Multiple months select ho sakte hain</small>
    </div>

    <!-- Apply Button -->
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100" onclick="loadChartData()">
            Apply
        </button>
    </div>
</div>
