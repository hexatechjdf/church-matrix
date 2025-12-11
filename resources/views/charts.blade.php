@extends('layouts.chart')

<div class="dashboard">
    <div class="filters">
        <div class="filter-group">
            <select id="campusFilter">
                <option value="">All Campuses</option>
                @foreach($campuses as $campus)
                <option value="{{ $campus->campus_unique_id }}">{{ $campus->campus_unique_id }}</option>
                @endforeach
            </select>
            <i class="fas fa-university"></i>
        </div>

        <div class="filter-group week-picker">
            <div class="week-trigger" id="weekTrigger">
                <span id="weekText">Week of Nov 30</span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="week-dropdown" id="weekDropdown">
                <div class="week-buttons">
                    <button class="week-btn active" data-week="0">This Week</button>
                    <button class="week-btn" data-week="-1">Last Week</button>
                    <button class="week-btn" data-week="-2">Two Weeks Ago</button>
                    <button class="week-btn" data-week="1">Next Week</button>
                </div>
                <div class="mini-calendar" id="miniCalendar"></div>
            </div>
        </div>

        <div class="filter-group">
            <select id="eventFilter">
                <option value="">All Events</option>
                @foreach($events as $event)
                <option value="{{ $event->event_unique_id }}">Event {{ $event->event_unique_id }}</option>
                @endforeach
            </select>
            <i class="fas fa-calendar-check"></i>
        </div>

        <div class="filter-group">
            <select id="categoryFilter">
                <option>All Categories</option>
                <option>Categories 1</option>
                <option>Categories 2</option>
                <option>Categories 3</option>
            </select>
            <i class="fas fa-tags"></i>
        </div>
    </div>

    <div class="chart-container">
          <div id="chart"></div>
        <!-- <div class="chart-wrapper"><canvas id="myChart"></canvas> -->

          
        </div>
    </div>
</div>