@extends('layouts.chart')


<div class="container">
  <div class="header">
    <h1>Planning Center</h1>
    <p>Planning Center</p>
  </div>

  <!-- Line Chart -->
  <div class="card">
    <div class="chart-title">Monthly Attendance Trend</div>
    <div id="lineAvg" class="text-center mb-3"></div>
    <div class="controls">
      <div><label>Year</label><select id="lineYearSelect" class="form-control"></select></div>
      <div><label>Months</label><select id="lineMonthSelect" multiple class="form-control"></select></div>
      <div><label>Events</label><select id="events-line" class="form-control"></select></div>
      <div><label>Attendance Types</label><select id="attendanceType-line" class="form-control"></select></div>
      <div class="col-xl-auto col-lg-12 col-md-12 mb-2">
        <label class="d-none d-lg-block">&nbsp;</label>
        <button id="lineResetBtn" class="btn btn-primary btn-block px-4">
          Reset
        </button>
      </div>
    </div>
    <div id="lineChart"></div>
  </div>

  <!-- Stacked Bar Chart -->
  <div class="card">
    <div class="chart-title">Attendance by Month (Events Stacked)</div>
    <div id="barAvg" class="text-center mb-3"></div>
    <div class="controls">
      <div><label>Year</label><select id="barYearSelect" class="form-control"></select></div>
      <div><label>Months</label><select id="barMonthSelect" multiple class="form-control"></select></div>
      <div><label>Events</label><select id="events-bar" class="form-control"></select></div>
      <div><label>Attendance Types</label><select id="attendanceType-bar" class="form-control"></select></div>
      <div class="col-xl-auto col-lg-12 col-md-12 mb-2">
        <label class="d-none d-lg-block">&nbsp;</label>
        <button id="barResetBtn" class="btn btn-primary btn-block px-4">
          Reset
        </button>
      </div>
    </div>
    <div id="barChart"></div>
  </div>

  <!-- Pie Chart -->
  <div class="card">
    <div class="chart-title">Yearly Attendance by Type</div>
    <div id="pieAvg" class="text-center mb-3"></div>

    <div class="controls">
      <div>
        <label>Year</label>
        <select id="pieYearSelect" class="form-control">
        </select>
      </div>
      <div>
        <label>Events</label>
        <select id="events-pie" class="form-control">
          <option value="">All Events</option>
        </select>
      </div>
      <button id="pieResetBtn" class="btn btn-primary btn-block px-4 reset">Reset</button>
    </div>
    <div id="pieChart"></div>
  </div>

  <!-- Event Chart -->
  <div class="card">
    <div class="chart-title">Attendance by Event (Months Stacked)</div>
    <div id="eventsAvg" class="text-center mb-3"></div>
    <div class="controls">
      <div><label>Year</label><select id="eventsYearSelect" class="form-control"></select></div>
      <div class="col-xl-auto col-lg-12 col-md-12 mb-2">
        <label class="d-none d-lg-block">&nbsp;</label>
        <button id="eventsResetBtn" class="btn btn-primary btn-block px-4">
          Reset
        </button>
      </div>
    </div>
    <div id="eventsChart"></div>
  </div>


  <!-- Guest chart -->
<div class="card">
    <div class="chart-title">Guest Attendance by Event</div>
    <div id="guestAvg" class="text-center mb-3"></div>
    <div class="controls">
        <div><label>Year</label><select id="guestYearSelect" class="form-control"></select></div>
        <div><label>Months</label><select id="guestMonthSelect" multiple class="form-control"></select></div>
        <div class="col-xl-auto col-lg-12 col-md-12 mb-2">
            <label class="d-none d-lg-block">&nbsp;</label>
            <button id="guestResetBtn" class="btn btn-primary btn-block px-4">
                Reset
            </button>
        </div>
    </div>
    <div id="guestChart"></div>
</div>
</div>