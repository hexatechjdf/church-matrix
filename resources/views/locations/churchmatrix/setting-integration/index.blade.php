@extends('layouts.app')

@section('title', 'Setting')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/setting_integration.css') }}">
@endsection

@section('content')

@include('button.index')

<div class="settings-content-wrapper">
    <div class="modules-section">
        <div class="container-fluid">
            
            <div class="modules-grid">
                
              <div class="module-card" onclick="showModule('events')">

                    <span class="stats-badge">Active</span>
                    <div class="icon-wrapper">
                        <i class="fas fa-calendar-check module-icon"></i>
                    </div>
                    <div class="module-content">
                        <h3 class="module-title">Events</h3>
                        <p class="module-desc">Create, manage, and track all church events and activities</p>
                    </div>
                    <i class="fas fa-chevron-right module-arrow"></i>
                </div>

                <div class="module-card" onclick="showModule('service-times')">
                    <span class="stats-badge">Active</span>
                    <div class="icon-wrapper">
                        <i class="fas fa-clock module-icon"></i>
                    </div>
                    <div class="module-content">
                        <h3 class="module-title">Service Time</h3>
                        <p class="module-desc">Schedule and organize service timings for your congregation</p>
                    </div>
                    <i class="fas fa-chevron-right module-arrow"></i>
                </div>

                <div class="module-card" onclick="showModule('records')">
                    <span class="stats-badge">Active</span>
                    <div class="icon-wrapper">
                        <i class="fas fa-database module-icon"></i>
                    </div>
                    <div class="module-content">
                        <h3 class="module-title">Records</h3>
                        <p class="module-desc">View, manage, and organize all church records securely</p>
                    </div>
                    <i class="fas fa-chevron-right module-arrow"></i>
                </div>

            </div>

            <div id="events-content" class="module-content-area">
                @include('events.index')
            </div>

            <div id="service-times-content" class="module-content-area">
                @include('service_times.index')
            </div>

            <div id="records-content" class="module-content-area">
                @include('records.index')
            </div>

        </div>
    </div>
</div>

<script>
function showModule(moduleName) {
    document.querySelectorAll('.module-content-area').forEach(area => {
        area.classList.remove('active');
    });
    
    document.querySelectorAll('.module-card').forEach(card => {
        card.classList.remove('active');
    });
    
    const contentArea = document.getElementById(moduleName + '-content');
    if (contentArea) {
        contentArea.classList.add('active');
        
        event.currentTarget.classList.add('active');
        
        contentArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
</script>

@endsection