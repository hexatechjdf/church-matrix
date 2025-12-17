<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Planning Center</title>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
      min-height: 100vh;
      padding: 30px 20px;
      color: #1e293b;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    .header h1 {
      font-size: 2.8rem;
      font-weight: 800;
      background: linear-gradient(90deg, #4f46e5, #7c3aed);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      margin-bottom: 10px;
    }

    .header p {
      font-size: 1.2rem;
      color: #64748b;
    }

    .card {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(16px);
      border-radius: 20px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
      padding: 28px;
      margin-bottom: 40px;
      border: 1px solid rgba(139, 92, 246, 0.1);
    }

    .controls {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      align-items: end;
      margin-bottom: 25px;
    }

    label {
      font-weight: 600;
      color: #4c1d95;
      font-size: 0.95rem;
      margin-bottom: 8px;
      display: block;
    }

    select,
    button {
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border: 2px solid #e0e7ff;
      background: white;
    }

    select:focus {
      border-color: #8b5cf6;
      box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.25);
    }

    .select2-container--default .select2-selection--multiple {
      border: 2px solid #e0e7ff !important;
      border-radius: 14px !important;
      min-height: 52px !important;
      padding: 8px 12px !important;
      background: white;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
      border-color: #8b5cf6 !important;
      box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2) !important;
    }

    .select2-selection__choice {
      background: linear-gradient(135deg, #8b5cf6, #a78bfa) !important;
      color: white !important;
      border-radius: 12px !important;
      padding: 6px 12px !important;
      font-weight: 600 !important;
    }

    .reset-btn {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      color: white;
      border: none;
      font-weight: 600;
      cursor: pointer;
      height: 52px;
    }

    .reset-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(139, 92, 246, 0.4);
    }

    .chart-title {
      text-align: center;
      font-size: 1.6rem;
      font-weight: 700;
      color: #4c1d95;
      margin-bottom: 20px;
    }

    #pieChart {
      max-width: 600px;
      margin: 0 auto;
    }

    @media (max-width: 768px) {
      .controls {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

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
  </div>

  <script>
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const fullMonthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    let lineChart = null;
    let barChart = null;
    let pieChart = null;
    let eventsChart = null;

    let defaultAttendances = [{
        id: 'regular',
        name: 'Regular',
        type: 'AttendanceType'
      },
      {
        id: 'volunteer',
        name: 'Volunteer',
        type: 'AttendanceType'
      },
      {
        id: 'guest',
        name: 'Guest',
        type: 'AttendanceType'
      }
    ];

    let currentAttendances = {};
    let eventsObject = {};

    // Helper functions for calculations
    function calculateTotal(seriesData) {
      const allValues = seriesData.flatMap(s => s.data || []);
      return allValues.reduce((a, b) => a + b, 0);
    }

    function calculatePieTotal(values) {
      return values.reduce((a, b) => a + b, 0);
    }

    function getEvents(offset) {
      let url = `{{route('locations.planningcenter.events') }}`;
      $.get(url + '?offset=' + offset)
        .done(function(res) {
          let events = res.data;

          ['line', 'bar', 'pie'].forEach(type => {
            const $select = $(`#events-${type}`);
            let allEventsHTML = `<option value="">All Events</option>`;

            for (let event of events) {
              eventsObject[event.id] = event;
              currentAttendances[event.id] = event.relationships?.attendance_types?.data.map(t => {
                return {
                  ...t,
                  ...res.included[t.type + '.' + t.id]?.attributes
                };
              });
              allEventsHTML += `<option value="${event.id}">${event.attributes.name}</option>`;
            }
            $select.html(allEventsHTML);
          });

        }).fail(function(res) {
          console.error('Failed to load events:', res);
        });
    }

    function addAttendanceTypes(selector, eventId = '') {
      const $select = $(selector);
      let html = `<option value="">All Types</option>`;

      defaultAttendances.forEach(type => {
        html += `<option value="${type.id}">${type.name}</option>`;
      });

      if (eventId && currentAttendances[eventId]) {
        currentAttendances[eventId].forEach(type => {
          html += `<option value="${type.id}">${type.name}</option>`;
        });
      }

      $select.html(html);
    }

    $('body').on('change', '[id^="events-"]', function() {
      const eventId = $(this).val();
      const panel = $(this).closest('.card');
      const attSelect = panel.find('[id^="attendanceType-"]');

      if (attSelect.length) {
        addAttendanceTypes(attSelect, eventId);
      }

      if (panel.find('#lineChart').length) loadChart('line');
      if (panel.find('#barChart').length) loadChart('bar');
      if (panel.find('#pieChart').length) loadChart('pie');
    });

    $('body').on('change', '[id^="attendanceType-"]', function() {
      const panel = $(this).closest('.card');
      if (panel.find('#lineChart').length) loadChart('line');
      if (panel.find('#barChart').length) loadChart('bar');
    });

    function loadChart(chartType) {
      let yearSelect, monthSelect, endpoint = '/get-chart-json';
      let total, count; 
      if (chartType === 'line') {
        yearSelect = '#lineYearSelect';
        monthSelect = '#lineMonthSelect';
       endpoint = '{{ route("locations.planningcenter.chart.json") }}';
      } else if (chartType === 'bar') {
        yearSelect = '#barYearSelect';
        monthSelect = '#barMonthSelect';
       endpoint = '{{ route("locations.planningcenter.chart.json") }}';
      } else if (chartType === 'pie') {
        yearSelect = '#pieYearSelect';
        monthSelect = null;
       endpoint = '{{ route("locations.planningcenter.pie.chart.data") }}';
      } else if (chartType === 'events') {
        yearSelect = '#eventsYearSelect';
        monthSelect = null;
       endpoint = '{{ route("locations.planningcenter.events.chart.data") }}';
      }

      const year = $(yearSelect).val() || new Date().getFullYear();
      const months = monthSelect ? $(monthSelect).val() : null;

      const panel = $(yearSelect).closest('.card');
      let eventId = '',
        attendanceId = '';

      if (chartType === 'pie') {
        eventId = panel.find('[id^="events-"]').val();
        const attSelect = panel.find('[id^="attendanceType-"]');
        if (attSelect.length) {
          attendanceId = attSelect.val();
        }
      } else if (chartType !== 'events') {
        eventId = panel.find('[id^="events-"]').val();
        attendanceId = panel.find('[id^="attendanceType-"]').val();
      }

      console.log(`Loading ${chartType} chart:`, {
        year: year,
        months: months,
        event_id: eventId,
        attendance_id: attendanceId
      });

      let params = {
        year: year
      };

      if (months && monthSelect) {
        params.months = months;
      }

      if (eventId && endpoint !== '/get-events-chart-data') {
        params.event_id = eventId;
      }

      if (attendanceId) {
        params.attendance_id = attendanceId;
      }

      $.get(endpoint, params)
        .done(function(res) {
          console.log(`${chartType} chart response:`, res);

          if (res.available_years || res.years) {
            $(yearSelect).empty();
            (res.available_years || res.years || []).forEach(y => {
              $(yearSelect).append(`<option value="${y}" ${y == year ? 'selected' : ''}>${y}</option>`);
            });
          }

          if (chartType === 'pie') {
            // Pie chart average
            total = res.values.reduce((a, b) => a + b, 0);
            count = res.values.length || 1;
            $(`#pieAvg`).text(`Average Attendance: ${Math.round(total / count)}`);

            if (!res.values || res.values.length === 0 || res.values.every(v => v === 0)) {
              res.labels = ['Regular', 'Guest', 'Volunteer'];
              res.values = [2847, 1022, 2009];
            }

            const totalAttendees = res.values.reduce((a, b) => a + b, 0);

            if (pieChart) {
              pieChart.destroy();
              pieChart = null;
            }

            const options = {
              chart: {
                type: 'pie',
                height: 500
              },
              series: res.values,
              labels: res.labels,
              title: {
                text: ``,
                align: 'center',
                style: {
                  fontSize: '20px',
                  fontWeight: 'bold',
                  color: '#4c1d95'
                }
              },
              legend: {
                position: 'top'
              },
              dataLabels: {
                enabled: true,
                formatter: function(val) {
                  return Math.round(val) + '%';
                }
              },
              tooltip: {
                y: {
                  formatter: function(val) {
                    return val.toLocaleString() + ' attendees';
                  }
                },
                footer: {
                  formatter: function() {
                    return '<div style="text-align:center; margin-top:8px; font-weight:bold;">Total: ' + totalAttendees.toLocaleString() + ' attendees</div>';
                  }
                }
              },
              colors: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444']
            };

            pieChart = new ApexCharts(document.getElementById('pieChart'), options);
            pieChart.render();

          } else if (chartType === 'events') {
            // Events chart average
            if (!res.series || res.series.length === 0) {
              const options = {
                chart: {
                  type: 'bar',
                  height: 400,
                  stacked: true,
                  toolbar: {
                    show: true
                  }
                },
                series: [],
                xaxis: {
                  categories: []
                },
                title: {
                  text: ``,
                  align: 'center',
                  style: {
                    fontSize: '18px',
                    color: '#4c1d95'
                  }
                },
                noData: {
                  text: 'No data available',
                  align: 'center',
                  verticalAlign: 'middle'
                }
              };

              if (eventsChart) {
                eventsChart.updateOptions(options);
                eventsChart.updateSeries([]);
              } else {
                eventsChart = new ApexCharts(document.getElementById('eventsChart'), options);
                eventsChart.render();
              }
              return;
            }

            // YEH LINE ADD KARO: Events average calculate
            const eventsTotal = res.series.flatMap(s => s.data).reduce((a, b) => a + b, 0);
            const eventCount = res.series.length || 1;
            const eventsAvg = Math.round(eventsTotal / eventCount);

            // YEH LINE ADD KARO: Average display
            $(`#eventsAvg`).text(`Average per Event: ${eventsAvg}`);

            const eventNames = res.series.map(s => s.name);
            const monthSeries = fullMonthNames.map((month, monthIndex) => {
              const monthData = eventNames.map(eventName => {
                const seriesItem = res.series.find(s => s.name === eventName);
                return seriesItem ? (seriesItem.data[monthIndex] || 0) : 0;
              });

              return {
                name: month,
                data: monthData
              };
            });

            const options = {
              chart: {
                type: 'bar',
                height: Math.max(600, eventNames.length * 40),
                stacked: true,
                toolbar: {
                  show: true
                }
              },
              plotOptions: {
                bar: {
                  horizontal: true,
                  borderRadius: 8
                }
              },
              series: monthSeries,
              xaxis: {
                categories: eventNames,
                title: {
                  text: 'Attendance'
                },
                labels: {
                  rotate: -45,
                  style: {
                    fontSize: '12px'
                  }
                }
              },
              yaxis: {
                title: {
                  text: 'Months'
                }
              },
              title: {
                text: ``,
                align: 'center',
                style: {
                  fontSize: '20px',
                  fontWeight: 'bold',
                  color: '#4c1d95'
                }
              },
              legend: {
                position: 'top'
              },
              tooltip: {
                y: {
                  formatter: function(val) {
                    return val.toLocaleString() + ' attendees';
                  }
                }
              },
              dataLabels: {
                enabled: true
              },
              colors: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FECA57', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2', '#F5576C', '#43AA8B']
            };

            if (eventsChart) {
              eventsChart.updateOptions(options);
              eventsChart.updateSeries(monthSeries);
            } else {
              eventsChart = new ApexCharts(document.getElementById('eventsChart'), options);
              eventsChart.render();
            }

          } else {
            const series = (res.series || []).map(item => ({
              name: item.name,
              data: item.data
            }));

            let chartTitle = ``;

            if (eventId) {
              const eventName = panel.find('[id^="events-"] option:selected').text();
              chartTitle += ` (${eventName})`;
            }
            if (attendanceId) {
              const attName = panel.find('[id^="attendanceType-"] option:selected').text();
              chartTitle += ` - ${attName}`;
            }

            if (chartType === 'line') {
              // Line chart average
              const lineTotal = series.flatMap(s => s.data).reduce((a, b) => a + b, 0);
              const monthCount = months ? months.length : 12;
              const lineAvg = Math.round(lineTotal / monthCount);

              $(`#lineAvg`).text(`Average Monthly: ${lineAvg}`);

              const options = {
                chart: {
                  type: 'area',
                  height: 500,
                  toolbar: {
                    show: true
                  }
                },
                series,
                stroke: {
                  curve: 'smooth',
                  width: 4
                },
                fill: {
                  opacity: 0.6,
                  type: 'gradient'
                },
                xaxis: {
                  categories: res.categories || []
                },
                yaxis: {
                  title: {
                    text: 'Attendance'
                  }
                },
                title: {
                  text: chartTitle,
                  align: 'center',
                  style: {
                    fontSize: '20px',
                    color: '#4c1d95'
                  }
                },
                legend: {
                  position: 'top'
                },
                colors: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#ec4899', '#f97316']
              };

              if (lineChart) {
                lineChart.updateOptions({
                  series,
                  xaxis: {
                    categories: res.categories || []
                  },
                  title: {
                    text: options.title.text
                  }
                });
              } else {
                lineChart = new ApexCharts(document.getElementById('lineChart'), options);
                lineChart.render();
              }

            } else {
              // Bar chart average
              const barTotal = series.flatMap(s => s.data).reduce((a, b) => a + b, 0);
              const barMonthCount = months ? months.length : 12;
              const barAvg = Math.round(barTotal / barMonthCount);

              // YEH LINE ADD KARO: Bar chart average display
              $(`#barAvg`).text(`Average Monthly: ${barAvg}`);

              const options = {
                chart: {
                  type: 'bar',
                  height: 600,
                  stacked: true,
                  toolbar: {
                    show: true
                  }
                },
                plotOptions: {
                  bar: {
                    horizontal: true,
                    borderRadius: 8
                  }
                },
                series,
                xaxis: {
                  categories: res.categories || [],
                  title: {
                    text: 'Attendance'
                  }
                },
                yaxis: {
                  title: {
                    text: 'Month'
                  }
                },
                title: {
                  text: chartTitle,
                  align: 'center',
                  style: {
                    fontSize: '20px',
                    color: '#4c1d95'
                  }
                },
                legend: {
                  position: 'top'
                },
                tooltip: {
                  y: {
                    formatter: val => val.toLocaleString() + ' attendees'
                  }
                },
                dataLabels: {
                  enabled: true
                },
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6366f1', '#ec4899', '#f97316']
              };

              if (barChart) {
                barChart.updateOptions({
                  series,
                  xaxis: {
                    categories: res.categories || []
                  },
                  title: {
                    text: options.title.text
                  }
                });
              } else {
                barChart = new ApexCharts(document.getElementById('barChart'), options);
                barChart.render();
              }
            }
          }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          console.error(`Error loading ${chartType} chart:`, textStatus, errorThrown);

          if (chartType === 'pie') {
            $(`#pieAvg`).text('Error loading data');
            const options = {
              chart: {
                type: 'pie',
                height: 500
              },
              series: [1],
              labels: ['Error Loading Data'],
              title: {
                text: 'Error',
                align: 'center',
                style: {
                  fontSize: '20px',
                  color: '#ff0000'
                }
              },
              legend: {
                position: 'bottom'
              }
            };

            if (pieChart) {
              pieChart.updateOptions(options);
              pieChart.updateSeries([1]);
            } else {
              pieChart = new ApexCharts(document.getElementById('pieChart'), options);
              pieChart.render();
            }
          } else if (chartType === 'events') {
            $(`#eventsAvg`).text('Error loading data');
            const options = {
              chart: {
                type: 'bar',
                height: 400,
                stacked: true,
                toolbar: {
                  show: true
                }
              },
              series: [],
              xaxis: {
                categories: []
              },
              title: {
                text: 'Error Loading Event Data',
                align: 'center',
                style: {
                  fontSize: '18px',
                  color: '#ff0000'
                }
              },
              noData: {
                text: 'Error loading data',
                align: 'center',
                verticalAlign: 'middle'
              }
            };

            if (eventsChart) {
              eventsChart.updateOptions(options);
              eventsChart.updateSeries([]);
            } else {
              eventsChart = new ApexCharts(document.getElementById('eventsChart'), options);
              eventsChart.render();
            }
          } else if (chartType === 'line') {
            $(`#lineAvg`).text('Error loading data');
          } else if (chartType === 'bar') {
            $(`#barAvg`).text('Error loading data');
          }
        });
    }

    $('#lineMonthSelect, #barMonthSelect').each(function() {
      $(this).select2({
        placeholder: "All months",
        allowClear: true,
        width: '100%'
      });
      fullMonthNames.forEach((m, i) => $(this).append(new Option(m, i + 1)));
    });

    $(document).ready(function() {
      getEvents(0);

      setTimeout(() => {
        ['line', 'bar'].forEach(type => {
          const attSelect = $(`#attendanceType-${type}`);
          if (attSelect.length) {
            addAttendanceTypes(attSelect);
          }
        });

        setTimeout(() => {
          loadChart('line');
          loadChart('bar');
          loadChart('pie');
          loadChart('events');
        }, 1000);
      }, 500);
    });

    $('#lineYearSelect, #lineMonthSelect').on('change', () => loadChart('line'));
    $('#lineResetBtn').on('click', () => {
      $('#lineYearSelect').val(new Date().getFullYear());
      $('#lineMonthSelect').val(null).trigger('change');
      $('#events-line').val('');
      $('#attendanceType-line').val('');
      addAttendanceTypes('#attendanceType-line');
      loadChart('line');
    });

    $('#barYearSelect, #barMonthSelect').on('change', () => loadChart('bar'));
    $('#barResetBtn').on('click', () => {
      $('#barYearSelect').val(new Date().getFullYear());
      $('#barMonthSelect').val(null).trigger('change');
      $('#events-bar').val('');
      $('#attendanceType-bar').val('');
      addAttendanceTypes('#attendanceType-bar');
      loadChart('bar');
    });

    $('#pieYearSelect').on('change', () => loadChart('pie'));
    $('#pieResetBtn').on('click', () => {
      $('#pieYearSelect').val(new Date().getFullYear());
      $('#events-pie').val('');
      loadChart('pie');
    });

    $('#eventsYearSelect').on('change', () => loadChart('events'));
    $('#eventsResetBtn').on('click', () => {
      $('#eventsYearSelect').val(new Date().getFullYear());
      loadChart('events');
    });
  </script>

</body>

</html>