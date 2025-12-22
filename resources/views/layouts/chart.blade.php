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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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

    #s2id_lineMonthSelect,
    .select2-container--default.select2-container--focus {
      width: 100% !important;
    }

  


    label {
      font-weight: 600;
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

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(139, 92, 246, 0.1);
    }

    .chart-title {
      text-align: left;
      font-size: 1.6rem;
      font-weight: 700;
      color: #535353;
      margin: 0;
    }

    .charts {
      text-align: right;
      font-size: 1rem;
      font-weight: 600;
      color: #000;
      padding: 8px 16px;
      border-radius: 10px;
      min-width: 150px;
    }



    #pieChart {
      max-width: 600px;
      margin: 0 auto;
    }

    .chart-loader {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 10;
      text-align: center;
    }

    .chart-container {
      position: relative;
      min-height: 300px;
    }

    .loader-spinner {
      width: 50px;
      height: 50px;
      border: 5px solid #e0e7ff;
      border-top: 5px solid #8b5cf6;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 15px;
    }

    .filter-tabs {
      grid-column: 1 / -1;
      background: rgba(240, 244, 255, 0.5);
      border-radius: 14px;
      padding: 20px;
      margin-bottom: 10px;
      border: 1px solid rgba(139, 92, 246, 0.1);
    }

    .tab-buttons {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      border-bottom: 2px solid #e0e7ff;
      padding-bottom: 10px;
    }

    .tab-btn {
      padding: 12px 24px;
      background: transparent;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      color: #64748b;
      transition: all 0.3s ease;
      position: relative;
    }

    .tab-btn:hover {
      color: #4f46e5;
      background: rgba(139, 92, 246, 0.1);
    }

    .tab-btn.active {
      color: #4f46e5;
      background: rgba(139, 92, 246, 0.15);
    }

    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -12px;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, #4f46e5, #7c3aed);
      border-radius: 3px;
    }

    .tab-content {
      display: none;
      animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
      display: block;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .controls {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      align-items: end;
      margin-bottom: 25px;
    }

    .filter-tabs {
      grid-column: 1 / -1;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .loader-text {
      color: #64748b;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .chart-content {
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .chart-loading .chart-content {
      opacity: 0.3;
      pointer-events: none;
    }

    @media (max-width: 768px) {
      .controls {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  @yield('content')

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

    function showLoader(chartType) {
      const loaderId = `${chartType}ChartLoader`;
      const cardId = `${chartType}ChartCard`;

      $(`#${loaderId}`).show();
      $(`#${cardId}`).addClass('chart-loading');
      console.log(`⏳ Showing loader for ${chartType} chart`);
    }

    function hideLoader(chartType) {
      const loaderId = `${chartType}ChartLoader`;
      const cardId = `${chartType}ChartCard`;

      $(`#${loaderId}`).hide();
      $(`#${cardId}`).removeClass('chart-loading');
    }

    $(document).ready(function() {
      ['line', 'bar', 'pie', 'events'].forEach(chartType => {
        $(`#${chartType}ChartLoader`).hide();
      });
    });

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
      showLoader(chartType);

      let yearSelect, monthSelect, endpoint;
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
      let months = null;
      if (monthSelect && $(monthSelect).length) {
        months = $(monthSelect).val();
      }

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

      let startDate = '';
      let endDate = '';

      if (chartType === 'line') {
        const activeTab = $('.tab-content.active').attr('id');

        if (activeTab === 'months-tab') {
          months = $('#lineMonthSelect').val();
          startDate = '';
          endDate = '';
        } else if (activeTab === 'date-range-tab') {
          const dateRangeVal = $('#lineDateRange').val();
          if (dateRangeVal && dateRangeVal.trim() !== '') {
            const dates = dateRangeVal.split(' to ');
            if (dates.length === 2) {
              startDate = dates[0].trim();
              endDate = dates[1].trim();
            }
          }
          months = null;
        }
      } else if (chartType === 'bar') {
        const dateRangeVal = $('#barDateRange').val();
        if (dateRangeVal && dateRangeVal.trim() !== '') {
          const dates = dateRangeVal.split(' to ');
          if (dates.length === 2) {
            startDate = dates[0].trim();
            endDate = dates[1].trim();
          }
        }
      }

      console.log(`Loading ${chartType} chart:`, {
        year: year,
        months: months,
        start_date: startDate,
        end_date: endDate,
        event_id: eventId,
        attendance_id: attendanceId
      });

      let params = {
        year: year
      };

      const hasDateRange = startDate && endDate && startDate !== '' && endDate !== '';
      const hasMonthFilter = months && months.length > 0;

      if (hasDateRange) {
        params.start_date = startDate;
        params.end_date = endDate;
      } else if (hasMonthFilter) {
        params.months = months;
      }

      if (eventId && endpoint !== '{{ route("locations.planningcenter.events.chart.data") }}') {
        params.event_id = eventId;
      }

      if (attendanceId && chartType !== 'events') {
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

            hideLoader('pie');

          } else if (chartType === 'events') {
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

              hideLoader('events');
              return;
            }

            const eventsTotal = res.series.flatMap(s => s.data).reduce((a, b) => a + b, 0);
            const eventCount = res.series.length || 1;
            const eventsAvg = Math.round(eventsTotal / eventCount);

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

            hideLoader('events');

          } else {
            // Line and Bar charts
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
              const lineTotal = series.flatMap(s => s.data).reduce((a, b) => a + b, 0);

              let categories = res.categories || [];
              const dataPoints = categories.length > 0 ? categories.length :
                (hasDateRange ? 1 : (hasMonthFilter ? months.length : 12));

              const lineAvg = dataPoints > 0 ? Math.round(lineTotal / dataPoints) : 0;

              let avgLabel = "Average";
              if (hasDateRange) {
                avgLabel = "Average (Selected Period)";
              } else if (hasMonthFilter) {
                avgLabel = `Average (${months.length} months)`;
              } else {
                avgLabel = "Average Monthly";
              }

              $(`#lineAvg`).text(`${avgLabel}: ${lineAvg}`);

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
                  categories: categories,
                  labels: {
                    rotate: hasDateRange ? -45 : 0,
                    style: {
                      fontSize: hasDateRange ? '10px' : '12px'
                    },
                    formatter: function(value) {
                      return value;
                    }
                  }
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
                tooltip: {
                  y: {
                    formatter: function(val) {
                      return val.toLocaleString() + ' attendees';
                    }
                  }
                },
                colors: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#ec4899', '#f97316']
              };

              if (lineChart) {
                lineChart.updateOptions({
                  series,
                  xaxis: {
                    categories: categories,
                    labels: options.xaxis.labels
                  },
                  title: {
                    text: options.title.text
                  }
                });
              } else {
                lineChart = new ApexCharts(document.getElementById('lineChart'), options);
                lineChart.render();
              }

              hideLoader('line');

            } else if (chartType === 'bar') {
              const barTotal = series.flatMap(s => s.data).reduce((a, b) => a + b, 0);

              const dataPoints = res.categories ? res.categories.length :
                (months ? months.length : 12);

              const barAvg = dataPoints > 0 ? Math.round(barTotal / dataPoints) : 0;

              let avgLabel = "Average";
              if (startDate && endDate) {
                avgLabel = "Average (Selected Period)";
              } else if (months && months.length > 0) {
                avgLabel = `Average (${months.length} months)`;
              } else {
                avgLabel = "Average Monthly";
              }

              $(`#barAvg`).text(`${avgLabel}: ${barAvg}`);

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
                  },
                  labels: {
                    rotate: hasDateRange ? -45 : 0,
                    style: {
                      fontSize: hasDateRange ? '10px' : '12px'
                    }
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
                    categories: res.categories || [],
                    labels: options.xaxis.labels
                  },
                  title: {
                    text: options.title.text
                  }
                });
              } else {
                barChart = new ApexCharts(document.getElementById('barChart'), options);
                barChart.render();
              }

              hideLoader('bar');
            }
          }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          console.error(`Error loading ${chartType} chart:`, textStatus, errorThrown);

          hideLoader(chartType);

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

      $('.tab-btn').on('click', function() {
        const tabId = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $(`#${tabId}`).addClass('active');
        if (tabId === 'months-tab') {
          $('#lineDateRange').val('');
        } else if (tabId === 'date-range-tab') {
          $('#lineMonthSelect').val(null).trigger('change');
        }

        loadChart('line');
      });

      $('#lineMonthSelect').on('change', function() {
        if ($(this).val() && $(this).val().length > 0) {
          $('.tab-btn').removeClass('active');
          $('.tab-content').removeClass('active');
          $('[data-tab="months-tab"]').addClass('active');
          $('#months-tab').addClass('active');
          $('#lineDateRange').val('');
        }
        loadChart('line');
      });

      $('#lineDateRange').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        opens: 'center',
        ranges: {
          'Today': [moment(), moment()],
          'This Week': [moment().startOf('week'), moment().endOf('week')],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'This Year': [moment().startOf('year'), moment().endOf('year')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()]
        },
        locale: {
          format: 'YYYY-MM-DD',
          cancelLabel: 'Clear',
          applyLabel: 'Apply'
        }
      });

      $('#barDateRange').daterangepicker({
        autoUpdateInput: false,
        showDropdowns: true,
        opens: 'center',
        ranges: {
          'Today': [moment(), moment()],
          'This Week': [moment().startOf('week'), moment().endOf('week')],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'This Year': [moment().startOf('year'), moment().endOf('year')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()]
        },
        locale: {
          format: 'YYYY-MM-DD',
          cancelLabel: 'Clear',
          applyLabel: 'Apply'
        }
      });

      $('#lineDateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));

        if ($(this).val()) {
          $('.tab-btn').removeClass('active');
          $('.tab-content').removeClass('active');
          $('[data-tab="date-range-tab"]').addClass('active');
          $('#date-range-tab').addClass('active');
          $('#lineMonthSelect').val(null).trigger('change');
        }
        loadChart('line');
      });

      $('#lineDateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        loadChart('line');
      });

      $('#barDateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        loadChart('bar');
      });




      $('#barDateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        loadChart('bar');
      });

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
      $('#lineDateRange').val('');
      $('#events-line').val('');
      $('#attendanceType-line').val('');

      $('.tab-btn').removeClass('active');
      $('.tab-content').removeClass('active');
      $('[data-tab="months-tab"]').addClass('active');
      $('#months-tab').addClass('active');

      addAttendanceTypes('#attendanceType-line');
      loadChart('line');
    });

    $('#barYearSelect, #barMonthSelect').on('change', () => loadChart('bar'));
    $('#barResetBtn').on('click', () => {
      $('#barYearSelect').val(new Date().getFullYear());
      $('#barMonthSelect').val(null).trigger('change');
      $('#barDateRange').val('');
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