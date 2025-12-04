@extends('layouts.location')

@section('title', 'Settings')



@section('content')
<div class="settings-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0">Settings</h1>
            <p class="text-muted mb-0">Configure your account preferences and integrations</p>
        </div>
        <div> @include('button.index') </div>
    </div>

    <!-- Timezone Settings -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card-modern">
                <div class="card-header-modern primary">
                    <h5>Timezone Configuration</h5>
                </div>
                <div class="card-body-modern">
                    <form action="{{ route('locations.churchmatrix.settings.timezone.save') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">
                                Select Timezone <span class="required">*</span>
                            </label>
                            <select name="timezone" id="timezone" class="form-control-modern" required>
                                <option value="">-- Choose Timezone --</option>
                                @foreach ($timezones as $tz => $label)
                                <option value="{{ $tz }}"
                                    {{ ($user->timezone ?? '') == $tz ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            <div class="helper-text">
                                <span>This will be used for all scheduling and time-based features</span>
                            </div>
                        </div>
                        <div class="card-footer-modern">
                            <button type="submit" class="btn-modern btn-primary">
                                <span>Save Timezone</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

     @include('components.church-matrix-keys')
    <!-- API Configuration -->
    {{-- <div id="admin-section">
        <div class="row">
            <!-- Church Matrix API -->
            <div class="col-lg-6">
                <form action="#">
                    <div class="card-modern">
                        <div class="card-header-modern info">
                            <h5>Church Matrix API</h5>
                        </div>
                        <div class="card-body-modern">
                            <div class="form-group">
                                <label class="form-label">
                                    User Auth (Email) <span class="required">*</span>
                                </label>
                                <input type="email" name="church_matrix_user" class="form-control-modern"
                                    placeholder="your-email@example.com" value="" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    Church Matrix API Key <span class="required">*</span>
                                </label>
                                <input type="text" name="church_matrix_api" class="form-control-modern"
                                    placeholder="Enter your API key" value="" required>
                                <div class="helper-text">
                                    <span>Your API key is encrypted and stored securely</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer-modern">
                            <button type="submit" class="btn-modern btn-info">
                                <span>Save API Credentials</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Region Selection -->
            <div class="col-lg-6">
                <div class="card-modern">
                    <div class="card-header-modern success">
                        <h5>Region Selection</h5>
                    </div>

                    <div class="card-body-modern">
                        <div class="alert-modern">
                            <div class="content">
                                <strong>Important</strong>
                                Please save your Church Matrix API credentials first before selecting a region.
                            </div>
                        </div>

                        <form action="#">
                            <div class="form-group">
                                <label class="form-label">
                                    Select Region <span class="required">*</span>
                                </label>
                                <select class="form-control-modern" name="region_id" id="region_id" disabled>
                                    <option value="">-- Choose Region --</option>
                                </select>
                                <div class="helper-text">
                                    <span>This determines your data center location</span>
                                </div>
                            </div>
                    </div>

                    <div class="card-footer-modern">
                        <button type="submit" class="btn-modern btn-success" disabled>
                            <span>Save Region</span>
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
</div>
@endsection
