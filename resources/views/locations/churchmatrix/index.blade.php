@extends('layouts.location')

@section('title', 'Settings')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8edf2 100%);
        min-height: 100vh;
    }

    .settings-container {
        max-width: 1300px;
    }

    .page-header {
        margin-bottom: 2.5rem;
    }

    .page-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-header p {
        color: #718096;
        font-size: 1.05rem;
    }

    .card-modern {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.75rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .card-modern:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-3px);
    }

    .card-header-modern {
        padding: 1.5rem 2rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header-modern.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-bottom: none;
    }

    .card-header-modern.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border-bottom: none;
    }

    .card-header-modern.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border-bottom: none;
    }

    .card-header-modern h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1a202c;
    }

    .card-header-modern.primary h5,
    .card-header-modern.info h5,
    .card-header-modern.success h5 {
        color: white;
    }

    .card-header-modern .icon {
        font-size: 1.5rem;
    }

    .card-body-modern {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-label .required {
        color: #e53e3e;
        margin-left: 3px;
    }

    .form-control-modern {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.2s ease;
        background: #fff;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control-modern:disabled {
        background-color: #f7fafc;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .form-control-modern::placeholder {
        color: #a0aec0;
    }

    .btn-modern {
        padding: 0.75rem 1.75rem;
        font-size: 0.95rem;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-modern:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    .btn-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
    }

    .btn-info:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 172, 254, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(17, 153, 142, 0.3);
    }

    .btn-success:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(17, 153, 142, 0.4);
    }

    .alert-modern {
        padding: 1rem 1.25rem;
        border-radius: 10px;
        border: 1px solid #bee3f8;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    }

    .alert-modern .icon {
        font-size: 1.25rem;
        color: #2c5282;
        flex-shrink: 0;
    }

    .alert-modern .content {
        color: #2c5282;
        line-height: 1.5;
        font-size: 0.9rem;
    }

    .alert-modern strong {
        font-weight: 600;
        display: block;
        margin-bottom: 0.25rem;
    }

    .card-footer-modern {
        padding: 1.25rem 2rem;
        background: #f7fafc;
        border-top: 1px solid #e2e8f0;
        text-align: right;
    }

    .helper-text {
        font-size: 0.85rem;
        color: #718096;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .helper-text .icon {
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.75rem;
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        .card-header-modern {
            padding: 1.25rem 1.5rem;
        }

        .btn-modern {
            width: 100%;
            justify-content: center;
        }
    }
</style>

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

    <!-- API Configuration -->
    <div id="admin-section">
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
    </div>
</div>
@endsection