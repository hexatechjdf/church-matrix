<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Planning Center • Full Analytics Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <style>
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

    .header h1 {
      font-size: 2.8rem;
      font-weight: 800;
      background: linear-gradient(90deg, #4f46e5, #7c3aed);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
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

    .chart-title {
      text-align: center;
      font-size: 1.6rem;
      font-weight: 700;
      color: #4c1d95;
    }

    .reset-btn {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      color: white;
      border: none;
      font-weight: 600;
      height: 52px;
      border-radius: 14px;
    }

    .reset-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(139, 92, 246, 0.4);
    }

    .controls {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      align-items: end;
      margin-bottom: 25px;
    }
    .chart-average{
        text-align: center;
    }
  </style>
</head>

<body>
    @yield('content')
    <script>
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const fullMonthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    let lineChart = null,
      barChart = null,
      pieChart = null,
      eventsChart = null;

      function calculateTotal(seriesData) {
      return seriesData.reduce((sum, s) => sum + s.data.reduce((a, b) => a + b, 0), 0);
    }

    let currentAttendances = {};
    let eventsObject = {};

    function loadAllEvents() {
      $.get("/locations/planningcenter/events")
        .done(function(res) {
          const events = res.data;
          ['line', 'bar', 'pie'].forEach(type => {
            const $select = $(`#events-${type}`);
            $select.html('<option value="">All Events</option>');
            events.forEach(e => {
              eventsObject[e.id] = e;
              currentAttendances[e.id] = (e.relationships?.attendance_types?.data || []).map(t => ({
                ...t,
                ...res.included[t.type + '.' + t.id].attributes
              }));
              $select.append(`<option value="${e.id}">${e.attributes.name}</option>`);
            });
          });
        });
    }

    $('body').on('change', '[id^="events-"]', function() {
      const panel = this.closest('.card');
      const attSelect = panel.querySelector('[id^="attendanceType-"]');
      if (!attSelect) return;
      const eventId = this.value;
      $(attSelect).html('<option value="">All Types</option>');
      if (eventId && currentAttendances[eventId]) {
        currentAttendances[eventId].forEach(t => {
          $(attSelect).append(`<option value="${t.id}">${t.name}</option>`);
        });
      }
    });

    function loadChart(type) {
      const yearSelect = `#${type}YearSelect`;
      const monthSelect = type === 'line' || type === 'bar' ? `#${type}MonthSelect` : null;
      const year = $(yearSelect).val() || new Date().getFullYear();
      const months = monthSelect ? $(monthSelect).val() : null;
      const endpoint = type === 'pie' ? '/apex-pie-data' : '/get-chart-json';

      $.get(endpoint, {
          year,
          months
        })
        .done(function(res) {
          $(yearSelect).empty();
          (res.available_years || []).forEach(y => $(yearSelect).append(`<option value="${y}" ${y == year ? 'selected' : ''}>${y}</option>`));

          const seriesData = (res.series || []).map(s => ({
            name: s.name,
            data: s.data
          }));

          let total = 0;
          let count = 0;
          if (type === 'pie') {
            total = res.values.reduce((a, b) => a + b, 0);
            count = res.values.length || 1;
            $(`#${type}Avg`).text(`Average attendance: ${Math.round(total / count)}`);
          } else if (type === 'events') {
            const eventNames = seriesData.map(s => s.name);
            total = calculateTotal(seriesData);
            count = eventNames.length || 1;
            $(`#${type}Avg`).text(`Average attendance: ${Math.round(total / count)}`);
          } else {
            total = calculateTotal(seriesData);
            count = (months ? months.length : 12) || 1;
            $(`#${type}Avg`).text(`Average attendance: ${Math.round(total / count)}`);
          }

          if (type === 'line') {
            const options = {
              chart: {
                type: 'area',
                height: 500
              },
              series: seriesData,
              xaxis: {
                categories: res.categories
              },
              title: {
                text: `${year} Trend`
              }
            };
            if (lineChart) lineChart.updateOptions(options);
            else {
              lineChart = new ApexCharts(document.getElementById('lineChart'), options);
              lineChart.render();
            }
          } else if (type === 'bar') {
            const options = {
              chart: {
                type: 'bar',
                height: 600,
                stacked: true
              },
              plotOptions: {
                bar: {
                  horizontal: true,
                  borderRadius: 8
                }
              },
              series: seriesData,
              xaxis: {
                categories: res.categories,
                title: {
                  text: 'Headcount'
                }
              },
              yaxis: {
                title: {
                  text: 'Month'
                }
              }
            };
            if (barChart) barChart.updateOptions(options);
            else {
              barChart = new ApexCharts(document.getElementById('barChart'), options);
              barChart.render();
            }
          } else if (type === 'pie') {
            const options = {
              chart: {
                type: 'pie',
                height: 500
              },
              series: res.values,
              labels: res.labels,
              title: {
                text: ``
              }
            };
            if (pieChart) pieChart.updateOptions(options);
            else {
              pieChart = new ApexCharts(document.getElementById('pieChart'), options);
              pieChart.render();
            }
          } else if (type === 'events') {
            const eventNames = seriesData.map(s => s.name);
            const monthSeries = fullMonthNames.map((m, i) => ({
              name: m,
              data: eventNames.map(name => {
                const s = seriesData.find(x => x.name === name);
                return s ? (s.data[i] || 0) : 0;
              })
            }));

            const options = {
              chart: {
                type: 'bar',
                height: Math.max(600, eventNames.length * 80),
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
                  text: 'Headcount'
                }
              },
              yaxis: {
                title: {
                  text: 'Events'
                },
                labels: {
                  maxWidth: 500
                }
              },
              title: {
                text: ``,
                align: 'center',
                style: {
                  fontSize: '18px',
                  color: '#4c1d95'
                }
              },
              legend: {
                position: 'top'
              },
              dataLabels: {
                enabled: true
              },
              colors: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FECA57', '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2', '#F5576C', '#43AA8B']
            };
            if (eventsChart) eventsChart.updateOptions(options);
            else {
              eventsChart = new ApexCharts(document.getElementById('eventsChart'), options);
              eventsChart.render();
            }
          }
        });
    }

    $('#lineMonthSelect, #barMonthSelect').select2({
      placeholder: "All months",
      allowClear: true
    });
    fullMonthNames.forEach((m, i) => $('#lineMonthSelect, #barMonthSelect').append(new Option(m, i + 1)));

    $('#lineYearSelect, #lineMonthSelect').on('change', () => loadChart('line'));
    $('#lineResetBtn').on('click', () => {
      $('#lineYearSelect').val(new Date().getFullYear());
      $('#lineMonthSelect').val(null).trigger('change');
      loadChart('line');
    });

    $('#barYearSelect, #barMonthSelect').on('change', () => loadChart('bar'));
    $('#barResetBtn').on('click', () => {
      $('#barYearSelect').val(new Date().getFullYear());
      $('#barMonthSelect').val(null).trigger('change');
      loadChart('bar');
    });

    $('#pieYearSelect').on('change', () => loadChart('pie'));
    $('#pieResetBtn').on('click', () => {
      $('#pieYearSelect').val(new Date().getFullYear());
      loadChart('pie');
    });

    $('#eventsYearSelect').on('change', () => loadChart('events'));
    $('#eventsResetBtn').on('click', () => {
      $('#eventsYearSelect').val(new Date().getFullYear());
      loadChart('events');
    });

    loadAllEvents();
    loadChart('line');
    loadChart('bar');
    loadChart('pie');
    loadChart('events');
  </script>
</body>

</html>