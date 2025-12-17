@extends('layouts.location')

@section('title', 'Settings')

@push('css')

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
        }
    </style>
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        .navbar {
            background: var(--bg-secondary) !important;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            color: var(--text-primary) !important;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .navbar-brand i {
            color: var(--primary-color);
        }

        .chart-card {
            /* background: var(--bg-card); */
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            /* border: 1px solid var(--border-color); */
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .chart-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            /* color: var(--text-primary); */
            color: black;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-title i {
            color: var(--primary-color);
        }

        .filter-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reset-btn {
            background: var(--warning-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
        }

        .filter-btn:active {
            transform: translateY(0);
        }

        .offcanvas {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border-left: 1px solid var(--border-color);
        }

        .offcanvas-header {
            border-bottom: 1px solid var(--border-color);
        }

        .offcanvas-title {
            color: var(--text-primary);
            font-weight: 600;
        }

        .btn-close {
            filter: invert(1);
        }

        .form-label {
            /* color: var(--text-primary); */
            color: black;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.65rem;
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            background: var(--bg-primary);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .form-select option {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .btn-apply-filter {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.2s;
        }

        .btn-apply-filter:hover {
            background: var(--primary-dark);
        }

        .btn-reset-filter {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            margin-top: 0.5rem;
            transition: all 0.2s;
        }

        .btn-reset-filter:hover {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .stat-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .stat-info p {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .apexcharts-tooltip {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
        }

        .apexcharts-tooltip-title {
            background: var(--bg-primary) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-md-12 mb-3">
                <a class="btn btn-secondary" href="{{ route('locations.churchmatrix.integration.records.index') }}">
                    <i class="fa-solid fa-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>

        {{-- <div class="row g-4 mb-4">
            <!-- Total Events -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="glass p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Events</p>
                            <h2 class="fw-bold">3</h2>
                        </div>
                    </div>
                    <div id="time_chart"></div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <i class="fa-solid fa-chart-line"></i>
                            Category-wise Attendance Trends
                        </h3>
                        <div class="d-flex">
                            <button class="filter-btn mx-3" data-target="#category_time_chart" data-coly="category_name"
                                data-type="category_time_chart" data-function="loadLiveData">
                                <i class="fa-solid fa-filter"></i>
                                Filter
                            </button>
                            <button class="reset-btn" data-target="#category_time_chart" data-coly="category_name"
                                data-function="loadLiveData">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div id="category_time_chart"></div>
                </div>
            </div>
            <div class="col-md-4">

                <!-- Pie Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <i class="fa-solid fa-chart-pie"></i>
                            Attendance
                        </h3>
                        <div class="d-flex">
                            <button class="filter-btn mx-3" data-target="#pie_chart" data-coly="category_name"
                                data-type="pie_chart" data-function="loadLivePieData">
                                <i class="fa-solid fa-filter"></i>
                                Filter
                            </button>
                            <button class="reset-btn" data-target="#pie_chart" data-coly="category_name"
                                data-function="loadLivePieData">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div id="pie_chart"></div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <i class="fa-solid fa-chart-column"></i>
                            Category based Comparison
                        </h3>
                        <div class="d-flex">
                            <button class="filter-btn mx-3" data-target="#weekly_chart" data-coly="category_name"
                                data-type="weekly_chart" data-function="loadGroupedBarChart">
                                <i class="fa-solid fa-filter"></i>
                                Filter
                            </button>
                            <button class="reset-btn" data-target="#weekly_chart" data-coly="category_name"
                                data-function="loadGroupedBarChart">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div id="weekly_chart"></div>
                </div>
            </div>

        </div> --}}

    @php($user = loginUser())

    <div class="modal fade" id="serviceTimeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="fetchselect2">
                    <form id="filterForm">

                        <!-- Date Range -->
                        <div class="mb-3 field-date-range d-none">
                            <label class="fw-bold mb-2">Select Date Range</label>
                            <input type="text" id="daterange" class="form-control" />
                        </div>

                        <!-- Category -->
                        <div class="category_field_area field-category d-none">
                            @include('locations.churchmatrix.components.categoryfield')
                        </div>

                        <!-- Campus -->
                        @if ($user->church_admin)
                            <div class="field-campus d-none">
                                @include('locations.churchmatrix.components.campusfields')
                            </div>
                        @endif

                        <!-- Year -->
                        <div class="mb-3 field-year d-none">
                            <label for="yearSelect" class="form-label">Year</label>
                            <select id="yearSelect" name="year" class="form-select select2">
                                <option value="">Select Year</option>
                                @foreach (array_reverse(getYears() ?? []) as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Month (Multiple) -->
                        <div class="mb-3 field-month d-none">
                            <label for="monthSelect" class="form-label">Month(s)</label>
                            <select id="monthSelect" name="months[]" class="form-select select2" multiple>
                                @foreach (getMonths() ?? [] as $index => $month)
                                    <option value="{{ $index + 1 }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </form>
                </div>
                <div class="card-body">
                    <div id="chart"></div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script')
    <script></script>
@endpush
