<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Apex Charts Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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
            <div class="col-md-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <i class="fa-solid fa-chart-area"></i>
                            Attendance Over Time
                        </h3>
                        <div class="d-flex">
                            <button class="filter-btn mx-3" data-target="#time_chart" data-coly="service_time"
                                data-type="time_chart" data-function="loadLiveData">
                                <i class="fa-solid fa-filter"></i> Filter
                            </button>

                            <button class="reset-btn btn" data-target="#time_chart" data-coly="service_time"
                                data-function="loadLiveData">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
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
        </div>
    </div>


    {{-- <div class="offcanvas offcanvas-end" tabindex="-1" id="chartFilterCanvas" aria-labelledby="offcanvasLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasLabel">Filter Options</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body" id="addServiceTimeForm">
            <div class="" id="fetchselect2">
                @include('locations.churchmatrix.records.components.chartsFilter')
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
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <!-- DateRangePicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(function() {
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                showDropdowns: true,
                opens: 'center', // instead of 'right', prevents modal overflow
                parentEl: '#serviceTimeModal', // <- attach dropdown to modal
                ranges: {
                    'Today': [moment(), moment()],
                    'This Week': [moment().startOf('week'), moment().endOf('week')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()]
                },
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: 'Clear'
                }
            });

            // Apply selection
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
            });

            // Clear selection
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @include('locations.churchmatrix.components.script')
    <script>
        let serverSideCall = {{ $user->church_admin ? 'false' : 'true' }};
        const filterConfig = {
            time_chart: ['category', 'year', 'month', 'campus'],
            category_time_chart: ['year', 'month', 'campus'],
            pie_chart: ['year', 'month', 'campus'],
            weekly_chart: ['date-range', 'campus']
        };



        function toggleFilterFields(type) {
            // Hide all fields first
            $('.field-category, .field-year, .field-month, .field-campus, .field-date-range')
                .addClass('d-none');

            // Show only the fields required for this chart
            (filterConfig[type] || []).forEach(field => {
                $(`.field-${field}`).removeClass('d-none');
            });
        }

        const charts = {
            time_chart: null,
            category_time_chart: null,
            pie_chart: null,
            weekly_chart: null
        };

        function showLoader(target) {
            $(target).html('<div class="text-center py-5">Loading...</div>');
        }



        function resetFilterForm() {
            const $form = $('#filterForm');
            $form[0].reset();
            $('#daterange').val('');

            $form.find('select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).val(null).trigger('change');
                }
            });
        }


        $(document).on('click', '.reset-btn', function() {

            // 1️⃣ Reset filters
            resetFilterForm();

            const target = $(this).data('target');
            const fnName = $(this).data('function');
            const coly = $(this).data('coly');
            const colx = 'month'; // default

            if (!window[fnName]) return;

            // 2️⃣ Show loader
            showLoader(target);

            // 3️⃣ Reload chart with default params
            window[fnName](colx, coly, target);
        });



        let activeChart = null;
        let columnx = 'month';
        let columny = 'attendance';

        $(document).on('click', '.filter-btn', function() {
            resetFilterForm();

            activeChart = {
                type: $(this).data('type'),
                fn: window[$(this).data('function')],
                target: $(this).data('target')
            };

            columnx = $(this).data('colx') ?? 'month';
            columny = $(this).data('coly');

            toggleFilterFields(activeChart.type);

            $('#serviceTimeModal').modal('show');

            $('#serviceTimeModal').on('shown.bs.modal', function() {
                initSelect2("#fetchselect2", "category");
                if (!serverSideCall) {
                    initSelect2("#fetchselect2", "campuses");
                }
            });
        });



        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            if (!activeChart || !activeChart.target) return;

            $('#serviceTimeModal').modal('hide');

            $('#serviceTimeModal').one('hidden.bs.modal', function() {
                activeChart.fn(columnx, columny, activeChart.target);
            });
        });


        function loadLiveData(colx, coly, target) {

            $.get('{{ route('locations.churchmatrix.integration.stats.month') }}', {
                year: $('#yearSelect').val(),
                months: $('#monthSelect').val() || [],
                category_id: $('.category-select').val(),
                campus_id: $('.campus-select').val(),
                colx,
                coly
            }).done(function(res) {
                const options = {
                    chart: {
                        type: 'line',
                        height: 520,
                        toolbar: {
                            show: true
                        }
                    },
                    series: res.series,
                    stroke: {
                        curve: 'smooth',
                        width: 4
                    },
                    markers: {
                        size: 6
                    },
                    xaxis: {
                        categories: res.categories,
                        title: {
                            text: 'Month'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Attendance Count'
                        }
                    },
                    legend: {
                        position: 'top'
                    },
                    title: {
                        text: '',
                        align: 'center'
                    }
                };

                if (charts[target]) {
                    charts[target].updateOptions({
                        series: res.series,
                        xaxis: {
                            categories: res.categories
                        }
                    });
                } else {
                    charts[target] = new ApexCharts(document.querySelector(target), options);
                    charts[target].render();
                }
            });
        }

        function loadLivePieData(colx, coly, target) {

            $.get('{{ route('locations.churchmatrix.integration.stats.month') }}', {
                year: $('#yearSelect').val(),
                months: $('#monthSelect').val() || [],
                campus_id: $('.campus-select').val(),
                colx,
                coly,
                chart: 'pie'
            }).done(function(res) {
                const options = {
                    chart: {
                        type: 'pie',
                        height: 420
                    },
                    series: res.series,
                    labels: res.labels,
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        text: '',
                        align: 'center'
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 300
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                if (charts[target]) {
                    charts[target].updateOptions({
                        series: res.series,
                        labels: res.labels
                    });
                } else {
                    charts[target] = new ApexCharts(document.querySelector(target), options);
                    charts[target].render();
                }
            });
        }

        function loadGroupedBarChart(colx, coly, target) {

            let daterange = $('#daterange').val() || '';
            $.get('{{ route('locations.churchmatrix.integration.stats.week') }}', {
                daterange,
                campus: $('.campus-select').val(),
                colx,
                coly
            }).done(function(res) {
                const options = {
                    chart: {
                        type: 'bar',
                        height: 520,
                        stacked: false
                    },
                    series: res.series,
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '60%'
                        }
                    },
                    xaxis: {
                        categories: res.categories,
                        title: {
                            text: 'Weeks'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Total Value'
                        }
                    },
                    legend: {
                        position: 'top'
                    },
                    title: {
                        text: 'Weekly Grouped Report',
                        align: 'center'
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };

                if (charts[target]) {
                    charts[target].updateOptions({
                        series: res.series,
                        xaxis: {
                            categories: res.categories
                        }
                    });
                } else {
                    charts[target] = new ApexCharts(document.querySelector(target), options);
                    charts[target].render();
                }
            });
        }
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                width: '100%',
                dropdownParent: $('#serviceTimeModal')
            });

        });






        // $('#yearSelect, #monthSelect').on('change', loadLiveData);


        loadLiveData('month', 'service_time', '#time_chart');
        loadLiveData('month', 'category_name', '#category_time_chart');
        loadLivePieData('month', 'category_name', '#pie_chart');
        loadGroupedBarChart('month', 'category_name', '#weekly_chart');
    </script>
    @stack('scripts')
</body>

</html>

{{-- @extends('layouts.location')

@section('title', 'Settings')

@push('css')
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .glass:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .icon-box {
            width: 55px;
            height: 55px;
            border-radius: 15px;
        }
    </style>
@endpush

@section('content')

    <div class="settings-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0">Church Attendance Records</h1>
                <p class="text-muted mb-0">Manage all records synced from Church Metrics</p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Total Events -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="glass p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Events</p>
                            <h2 class="fw-bold">3</h2>
                        </div>
                        <div class="icon-box bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avg Attendance -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="glass p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Avg Attendance</p>
                            <h2 class="fw-bold">504.5</h2>
                        </div>
                        <div class="icon-box bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>



        <div class="">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
                style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

                <div class="card-header bg-gradient-primary text-white border-0 py-4 d-flex justify-content-between">
                    <h4 class="mb-0 fw-bold">
                        <i class="me-3"></i>All Attendances
                    </h4>
                </div>
                <div class="card-body">
                    <div id="chart"></div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script')
    <script>
        let chart = null;

        function loadLiveData() {
            const year = $('#yearSelect').val() || new Date().getFullYear();
            const months = $('#monthSelect').val();

            let urll = '{{ route('locations.churchmatrix.integration.stats.month') }}'

            $.get(urll, {
                    year,
                    months
                })
                .done(function(res) {

                    const $yearSelect = $('#yearSelect').empty();
                    res.available_years.forEach(y => {
                        $yearSelect.append(`<option value="${y}" ${y == year ? 'selected' : ''}>${y}</option>`);
                    });

                    const series = res.series.map(item => ({
                        name: item.name,
                        data: item.data
                    }));

                    const options = {
                        chart: {
                            type: 'line',
                            height: 520,
                            toolbar: {
                                show: true
                            }
                        },
                        series,
                        stroke: {
                            curve: 'smooth',
                            width: 4
                        },
                        markers: {
                            size: 6
                        },
                        xaxis: {
                            categories: res.categories,
                            title: {
                                text: 'Month'
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Attendance Count'
                            }
                        },
                        legend: {
                            position: 'top'
                        },
                        title: {
                            text: `${year} Attendance by Service Time`,
                            align: 'center'
                        }
                    };

                    if (chart) {
                        chart.updateOptions({
                            series,
                            xaxis: {
                                categories: res.categories
                            },
                            title: options.title
                        });
                    } else {
                        chart = new ApexCharts(document.querySelector("#chart"), options);
                        chart.render();
                    }
                });
        }

        // $('#yearSelect, #monthSelect').on('change', loadLiveData);
        loadLiveData();
    </script>
@endpush --}}
