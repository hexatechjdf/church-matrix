@extends('layouts.location')

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
            </div>
        </div>
    </div>


@endsection

@push('script')
    <script></script>
@endpush
