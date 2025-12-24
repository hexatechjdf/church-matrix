@extends('layouts.chart')


<div class="container">
  <div class="row">
    <div class="col-md-12 mb-3">
      <a class="btn btn-secondary" href="{{ route('locations.planningcenter.headcounts.index') }}">
        <i class="fa-solid fa-arrow-left me-2"></i> Back
      </a>
    </div>
  </div>

  <div class="card" id="lineChartCard">
    <div class="chart-header">
      <div class="chart-title">Monthly Attendance Trends by Category</div>
      <div id="lineAvg" class="chart-average charts"></div>
    </div>

    <div class="chart-loader" id="lineChartLoader">
      <div class="loader-spinner"></div>
      <div class="loader-text">Loading chart data...</div>
    </div>

    <div class="chart-content">
      <div class="controls">

        <div class="filter-tabs">
          <div class="tab-buttons">
            <button type="button" class="tab-btn active" data-tab="months-tab">Months Filter</button>
            <button type="button" class="tab-btn" data-tab="date-range-tab">Custom Date Range</button>
          </div>

          <div class="tab-content active" id="months-tab">
            <div class="row">
              <div class="col-md-4">
                <label>Year</label>
                <select id="lineYearSelect" class="form-control"></select>
              </div>

              <div class="col-md-8">
                <label>Select Months</label>
                <select id="lineMonthSelect" multiple class="form-control"></select>
              </div>
            </div>
          </div>


          <div class="tab-content" id="date-range-tab">
            <div class="mb-3">
              <label>Custom Date Range</label>
              <input type="text" id="lineDateRange" class="form-control" placeholder="Select start and end date">
            </div>
          </div>
        </div>

        <div><label>Events</label><select id="events-line" class="form-control"></select></div>
        <div><label>Attendance Types</label><select id="attendanceType-line" class="form-control"></select></div>
        <div class="col-xl-auto col-lg-12 col-md-12">
          <label class="d-none d-lg-block">&nbsp;</label>
          <button id="lineResetBtn" class="btn btn-primary btn-block px-4">
            Reset
          </button>
        </div>
      </div>
      <div class="chart-container">
        <div id="lineChart"></div>
      </div>
    </div>
  </div>

  <!-- Stacked Bar Chart -->
  <div class="card" id="barChartCard">
    <div class="chart-header">
    <div class="chart-title">Attendance Breakdown by month</div>
    <div id="barAvg" class="charts text-center mb-3"></div>
    </div>

    <div class="chart-loader" id="barChartLoader">
      <div class="loader-spinner"></div>
      <div class="loader-text">Loading chart data...</div>
    </div>

    <div class="chart-content">
      <div class="controls">

        <div class="filter-tabs">
          <div class="tab-buttons">
            <button type="button" class="tab-btn active" data-tab="bar-months-tab">Months Filter</button>
            <button type="button" class="tab-btn" data-tab="bar-date-range-tab">Custom Date Range</button>
          </div>

          <div class="tab-content active" id="bar-months-tab">
            <div class="row">
              <div class="col-md-4">
                <label>Year</label>
                <select id="barYearSelect" class="form-control"></select>
              </div>

              <div class="col-md-8">
                <label>Select Months</label>
                <select id="barMonthSelect" multiple class="form-control"></select>
              </div>
            </div>
          </div>


          <div class="tab-content" id="bar-date-range-tab">
            <div class="mb-3">
              <label>Custom Date Range</label>
              <input type="text" id="barDateRange" class="form-control" placeholder="Select start and end date">
            </div>
          </div>
        </div>

        <div><label>Events</label><select id="events-bar" class="form-control"></select></div>
        <div><label>Attendance Types</label><select id="attendanceType-bar" class="form-control"></select></div>
        <div class="col-xl-auto col-lg-12 col-md-12">
          <label class="d-none d-lg-block">&nbsp;</label>
          <button id="barResetBtn" class="btn btn-primary btn-block px-4">
            Reset
          </button>
        </div>
      </div>
      <div class="chart-container">
        <div id="barChart"></div>
      </div>
    </div>
  </div>
  <!-- Pie Chart -->
  <div class="card" id="pieChartCard">
    <div class="chart-header">
    <div class="chart-title">Yearly Attendance by Category</div>
    <div id="pieAvg" class="charts text-center mb-3"></div>
    </div>

    <div class="chart-loader" id="pieChartLoader">
      <div class="loader-spinner"></div>
      <div class="loader-text">Loading chart data...</div>
    </div>

    <div class="chart-content">
      <div class="controls">
        <div>
          <label>Year</label>
          <select id="pieYearSelect" class="form-control"></select>
        </div>
        <div>
          <label>Events</label>
          <select id="events-pie" class="form-control">
            <option value="">All Events</option>
          </select>
        </div>
        <button id="pieResetBtn" class="btn btn-primary btn-block px-4">
          Reset
        </button>
      </div>
      <div class="chart-container">
        <div id="pieChart"></div>
      </div>
    </div>
  </div>

  <!-- Event Chart -->
  {{-- <div class="card" id="eventsChartCard">
    <div class="chart-header">
    <div class="chart-title">Attendance by Event (Months Stacked)</div>
    <div id="eventsAvg" class="charts text-center mb-3"></div>
    </div>

    <div class="chart-loader" id="eventsChartLoader">
      <div class="loader-spinner"></div>
      <div class="loader-text">Loading chart data...</div>
    </div>

    <div class="chart-content">
      <div class="controls">
        <div><label>Year</label><select id="eventsYearSelect" class="form-control"></select></div>
        <div class="col-xl-auto col-lg-12 col-md-12">
          <label class="d-none d-lg-block">&nbsp;</label>
          <button id="eventsResetBtn" class="btn btn-primary btn-block px-4">
            Reset
          </button>
        </div>
      </div>
      <div class="chart-container">
        <div id="eventsChart"></div>
      </div>
    </div>

  </div> --}}
</div>
