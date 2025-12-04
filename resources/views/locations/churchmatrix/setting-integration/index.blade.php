@extends('layouts.location')

@section('title', 'Settings')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/setting_integration.css') }}">
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

        .module-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .module-card.active {
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

        .icon-wrapper.events    { background: #ede9fe; }
        .icon-wrapper.service   { background: #fce7f3; }
        .icon-wrapper.records   { background: #dbeafe; }

        .module-icon {
            font-size: 1.75rem;
        }

        .icon-wrapper.events .module-icon   { color: #8b5cf6; }
        .icon-wrapper.service .module-icon  { color: #ec4899; }
        .icon-wrapper.records .module-icon  { color: #3b82f6; }

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
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .module-content-area.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('content')

<div class="settings-container">

    <!-- Header -->
   <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0">Settings</h1>
            <p class="text-muted mb-0">Configure your account preferences and integrations</p>
        </div>
        <div> @include('button.index') </div>
    </div>

    <!-- Modules Grid -->
    <div class="modules-grid">

        <div class="module-card active" data-module="events">
            <div class="icon-wrapper events">
                <i class="fas fa-calendar-check module-icon"></i>
            </div>
            <div class="module-content">
                <h3 class="module-title">Events</h3>
                <p class="module-desc">Create, manage, and track all church events and activities</p>
            </div>
            <i class="fas fa-chevron-right module-arrow"></i>
        </div>

        <div class="module-card" data-module="service-times">
            <div class="icon-wrapper service">
                <i class="fas fa-clock module-icon"></i>
            </div>
            <div class="module-content">
                <h3 class="module-title">Service Time</h3>
                <p class="module-desc">Schedule and organize service timings for your congregation</p>
            </div>
            <i class="fas fa-chevron-right module-arrow"></i>
        </div>

        <div class="module-card" data-module="records">
            <div class="icon-wrapper records">
                <i class="fas fa-database module-icon"></i>
            </div>
            <div class="module-content">
                <h3 class="module-title">Records</h3>
                <p class="module-desc">View, manage, and organize all church records securely</p>
            </div>
            <i class="fas fa-chevron-right module-arrow"></i>
        </div>

    </div>

    <!-- Content Areas -->
    <div id="events-content" class="module-content-area active">
        @include('locations.churchmatrix.events.index')
    </div>

    <div id="service-times-content" class="module-content-area">
        @include('locations.churchmatrix.service_times.index')
    </div>

    <div id="records-content" class="module-content-area">
        @include('locations.churchmatrix.records.index')
    </div>

</div>

</div>

<script>
    document.querySelectorAll('.module-card').forEach(card => {
        card.addEventListener('click', function () {
            const module = this.getAttribute('data-module');

            // Remove active class from all cards & content
            document.querySelectorAll('.module-card').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.module-content-area').forEach(area => area.classList.remove('active'));

            // Add active class to clicked card and corresponding content
            this.classList.add('active');
            const content = document.getElementById(module + '-content');
            if (content) {
                content.classList.add('active');
                content.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

</script>
@endsection