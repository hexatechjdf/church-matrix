@extends('layouts.location')

@section('title', 'Planning Center')
@push('style')
    <style>
        .settings-container {
            max-width: 1300px;
        }

        .settings-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .module-card-ajax {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .module-card-ajax:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .module-card-ajax.active {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .stats-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #10b981;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .icon-wrapper.events {
            background: #ede9fe;
        }

        .icon-wrapper.service {
            background: #fce7f3;
        }

        .icon-wrapper.records {
            background: #dbeafe;
        }

        .module-icon {
            font-size: 1.75rem;
        }

        .icon-wrapper.events .module-icon {
            color: #8b5cf6;
        }

        .icon-wrapper.service .module-icon {
            color: #ec4899;
        }

        .icon-wrapper.records .module-icon {
            color: #3b82f6;
        }

        .module-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .module-desc {
            color: #6b7280;
            font-size: 0.875rem;
            margin: 0;
        }

        .module-arrow {
            position: absolute;
            bottom: 1.5rem;
            right: 1.5rem;
            color: #9ca3af;
        }

        .module-content-area {
            display: none;
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .module-content-area.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush
@section('content')


    @include('locations.components.topbar')
    <div class="p-4">
        @php($active = $active ?? null)
        @php($campuses = $campuses ?? null)
        <div class="modules-grid">
            <div class="row g-4">

                {{-- Load All Head Counts --}}
                <div class="col-md-6">
                    <div class="module-card-ajax {{ $active == 'events' ? 'active' : '' }}" data-action="fetch-previous"
                        data-module="events">
                        <div class="icon-wrapper events">
                            <i class="fas fa-users module-icon"></i>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title">Fetch Previous Attendance</h3>
                            <p class="module-desc">
                                Load and sync all historical Attendance data
                            </p>
                        </div>
                        <i class="fas fa-chevron-right module-arrow"></i>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="module-card-ajax {{ $active == 'times' ? 'active' : '' }}" data-action="attendance-date"
                        data-module="service-times">
                        <div class="icon-wrapper service">
                            <i class="fas fa-calendar-day module-icon"></i>
                        </div>
                        <div class="module-content">
                            <h3 class="module-title">Attendance by Date</h3>
                            <p class="module-desc">
                                View and manage attendance counts for specific date
                            </p>
                        </div>
                        <i class="fas fa-chevron-right module-arrow"></i>
                    </div>
                </div>

            </div>


        </div>

    </div>

    <!-- Date / Year Selection Modal -->
    <div class="modal fade" id="dateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header" style="background:#E75037;color:#fff">
                    <h5 class="modal-title" id="dateModalLabel">Confirm Action</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="dateForm">

                        {{-- Date field --}}
                        <div class="form-group d-none" id="dateField">
                            <label>Select Date</label>
                            <input type="date" class="form-control" id="selectedDate">
                        </div>

                        {{-- Info text for fetch previous --}}
                        <div class="form-group d-none" id="infoField">
                            <p class="text-muted mb-0">
                                Click submit to fetch previous records
                            </p>
                        </div>

                        <input type="hidden" id="actionType">
                    </form>
                </div>


                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="submitDateBtn" style="background:#E75037;border-color:#E75037">
                        Submit
                    </button>
                </div>

            </div>
        </div>
    </div>


@endsection
@push('script')
    <script></script>

    <script>
        $(document).ready(function() {

            $('.module-card-ajax').on('click', function() {

                let action = $(this).data('action');
                $('#actionType').val(action);

                $('#dateField, #infoField').addClass('d-none');

                if (action === 'attendance-date') {
                    $('#dateModalLabel').text('Select Date');
                    $('#dateField').removeClass('d-none');

                    let today = new Date().toISOString().split('T')[0];
                    $('#selectedDate').val(today);
                }

                if (action === 'fetch-previous') {
                    $('#dateModalLabel').text('Fetch Previous Records');
                    $('#infoField').removeClass('d-none');
                }

                $('#dateModal').modal('show');
            });

            let isRequestRunning = false;

            $('#submitDateBtn').on('click', function() {

                if (isRequestRunning) return;

                let action = $('#actionType').val();
                let payload = {};

                if (action === 'attendance-date') {
                    payload = {
                        type: 'date',
                        date: $('#selectedDate').val()
                    };
                }

                if (action === 'fetch-previous') {
                    payload = {
                        type: 'year' // 🔥 ONLY THIS
                    };
                }

                isRequestRunning = true;

                const $btn = $('#submitDateBtn');
                $btn.prop('disabled', true).text('Processing...');

                runAjax(payload)
                    .always(() => {
                        isRequestRunning = false;
                        $btn.prop('disabled', false).text('Submit');
                        $('#dateModal').modal('hide');
                    });
            });

            function runAjax(payload) {

                return $.ajax({
                    url: '{{ route('fetch.headcounts') }}',
                    method: 'GET',
                    data: payload,
                    success(res) {
                        toastr.success('Successfully Submitted');
                    },
                    error(err) {
                        toastr.error('Something went wrong');
                    }
                });
            }

        });
    </script>
@endpush
