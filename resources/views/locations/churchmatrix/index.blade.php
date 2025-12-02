@extends('layouts.location')

@section('title', 'Setting')

@push('style')
    <style>
        .premium-card {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            transition: 0.3s ease-in-out;
        }

        .premium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.15);
        }

        .premium-header {
            background: linear-gradient(135deg, #5C8DFF, #2651A3);
            padding: 18px 22px;
            color: white;
        }

        .premium-header h5 {
            font-weight: 600;
            margin: 0;
        }

        .premium-body {
            padding: 25px;
            background: #ffffff;
        }

        .option-box {
            background: #f4f7fc;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: 0.25s ease;
        }

        .option-box:hover {
            background: #e8eef9;
        }

        .option-box input {
            transform: scale(1.3);
            margin-right: 12px;
        }

        .option-box label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: #E9F0F0">
                    <h5>Timezone</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('locations.churchmatrix.settings.timezone.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="timezone">Select Timezone</label>
                            <select name="timezone" id="timezone" class="form-control" required>
                                <option value="">-- Choose Timezone --</option>
                                @foreach ($timezones as $tz => $label)
                                    <option value="{{ $tz }}"
                                        {{ ($user->timezone ?? '') == $tz ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Save Timezone</button>
                    </form>
                    <div class="form-group" style="max-width: 350px;">
                        <label class="premium-header d-flex align-items-center"
                            style="cursor: pointer; border-radius: 12px; padding: 10px 15px;">
                            <i class="fas fa-user-shield mr-2" style="font-size: 20px;"></i>
                            <span>Do you want to be Church Matrix Admin?</span>
                        </label>
                        <div class="d-flex mt-2">
                            <div class="form-check mr-3" style="width: auto;">
                                <input class="form-check-input" type="radio" name="is_admin" id="admin_yes"
                                    value="yes">
                                <label class="form-check-label" for="admin_yes">Yes</label>
                            </div>
                            <div class="form-check" style="width: auto;">
                                <input class="form-check-input" type="radio" name="is_admin" id="admin_no" value="no"
                                    checked>
                                <label class="form-check-label" for="admin_no">No</label>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>

    <div id="admin-section">
        <div class="col-lg-12 mb-4">
            <div class="row">
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-md-6">

                            <form action="#">
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Church Matrix API</h5>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-group">
                                            <label>User Auth (Email) <span class="text-danger">*</span></label>
                                            <input type="email" name="church_matrix_user" class="form-control"
                                                value="">
                                        </div>

                                        <div class="form-group">
                                            <label>Church Matrix API Key <span class="text-danger">*</span></label>
                                            <input type="text" name="church_matrix_api" class="form-control"
                                                value="">
                                        </div>

                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-primary">Save API</button>
                                    </div>
                                </div>
                            </form>

                        </div>

                        <div class="col-md-6 d-flex flex-column">

                            <div class="card shadow-sm flex-fill d-flex flex-column">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Select Region</h5>
                                </div>

                                <div class="card-body flex-fill">

                                    <!-- Static message -->
                                    <div class="alert alert-info small mb-3">
                                        Please save your Church Metrics API credentials first.
                                    </div>

                                    <form action="#">

                                        <div class="form-group">
                                            <label>Select Region <span class="text-danger">*</span></label>
                                            <select class="form-control" name="region_id" id="region_id" disabled>
                                                <option value="">-- Choose Region --</option>
                                            </select>
                                        </div>

                                </div>

                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-success" disabled>Save Region</button>
                                </div>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection


@push('script')
    <script>
        const adminSection = document.getElementById('admin-section');
        const adminYes = document.getElementById('admin_yes');
        const adminNo = document.getElementById('admin_no');

        function toggleAdminSection() {
            if (adminYes.checked) {
                adminSection.style.display = 'block';
            } else {
                adminSection.style.display = 'none';
            }
        }

        adminYes.addEventListener('change', toggleAdminSection);
        adminNo.addEventListener('change', toggleAdminSection);

        // Initialize display
        toggleAdminSection();
    </script>
@endpush
