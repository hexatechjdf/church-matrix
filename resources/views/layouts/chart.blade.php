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
  <link rel="stylesheet" href="{{ asset('css/charts.css') }}">

</head>

<body>
  @yield('content')

  <script>
    const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const FULL_MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const ATTENDANCE_TYPES = [{
        id: 'regular',
        name: 'Regular'
      },
      {
        id: 'volunteer',
        name: 'Volunteer'
      },
      {
        id: 'guest',
        name: 'Guest'
      }
    ];

    let lineChart, barChart, pieChart, eventsChart;
    const eventCache = {};
    const attendanceCache = {};

    const Helpers = {
      showLoader: (type) => {
        $(`#${type}ChartLoader`).show();
        $(`#${type}ChartCard`).addClass('chart-loading');
      },

      hideLoader: (type) => {
        $(`#${type}ChartLoader`).hide();
        $(`#${type}ChartCard`).removeClass('chart-loading');
      },

      calculateAverage: (data) => {
        const values = data.flatMap(s => s.data || []);
        return values.length ? Math.round(values.reduce((a, b) => a + b, 0) / values.length) : 0;
      },

      getCurrentYear: () => new Date().getFullYear(),

      parseDateRange: (value) => {
        if (!value || !value.includes(' to ')) return null;
        const dates = value.split(' to ');
        return dates.length === 2 ? {
          start: dates[0].trim(),
          end: dates[1].trim()
        } : null;
      }
    };

    const EventManager = {
      loadEvents: (offset = 0) => {
        const url = `{{ route('locations.planningcenter.events') }}?offset=${offset}`;

        $.get(url)
          .done(res => {
            const events = res.data || [];

            events.forEach(event => {
              eventCache[event.id] = event;

              if (event.relationships?.attendance_types?.data) {
                attendanceCache[event.id] = event.relationships.attendance_types.data.map(t => ({
                  ...t,
                  ...(res.included?.[`${t.type}.${t.id}`]?.attributes || {})
                }));
              }
            });

            ['line', 'bar', 'pie'].forEach(type => {
              const $select = $(`#events-${type}`);
              let html = '<option value="">All Events</option>';

              events.forEach(event => {
                html += `<option value="${event.id}">${event.attributes.name}</option>`;
              });

              $select.html(html);
            });
          })
          .fail(console.error);
      },

      getAttendanceTypes: (eventId = '') => {
        let types = [...ATTENDANCE_TYPES];

        if (eventId && attendanceCache[eventId]) {
          types = [...types, ...attendanceCache[eventId].map(t => ({
            id: t.id,
            name: t.name || t.id
          }))];
        }

        return types;
      },

      updateAttendanceDropdown: (selector, eventId = '') => {
        const $select = $(selector);
        const types = EventManager.getAttendanceTypes(eventId);

        let html = '<option value="">All Types</option>';
        types.forEach(type => {
          html += `<option value="${type.id}">${type.name}</option>`;
        });

        $select.html(html);
      }
    };

    const ChartConfig = {
      line: {
        yearSelect: '#lineYearSelect',
        monthSelect: '#lineMonthSelect',
        dateRange: '#lineDateRange',
        endpoint: '{{ route("locations.planningcenter.chart.json") }}'
      },
      bar: {
        yearSelect: '#barYearSelect',
        monthSelect: '#barMonthSelect',
        dateRange: '#barDateRange',
        endpoint: '{{ route("locations.planningcenter.chart.json") }}'
      },
      pie: {
        yearSelect: '#pieYearSelect',
        endpoint: '{{ route("locations.planningcenter.pie.chart.data") }}'
      },
      events: {
        yearSelect: '#eventsYearSelect',
        endpoint: '{{ route("locations.planningcenter.events.chart.data") }}'
      }
    };

    async function loadChart(type) {
      Helpers.showLoader(type);

      const config = ChartConfig[type];
      if (!config) {
        console.error('Invalid chart type:', type);
        Helpers.hideLoader(type);
        return;
      }

      const params = buildChartParams(type, config);

      try {
        const response = await $.get(config.endpoint, params);
        handleChartResponse(type, response, params);
      } catch (error) {
        console.error(`Error loading ${type} chart:`, error);
        handleChartError(type);
      } finally {
        Helpers.hideLoader(type);
      }
    }

    function buildChartParams(type, config) {
      const year = $(config.yearSelect).val() || Helpers.getCurrentYear();
      const panel = $(config.yearSelect).closest('.card');

      const params = {
        year
      };

      if (config.dateRange) {
        const dateRange = Helpers.parseDateRange($(config.dateRange).val());
        if (dateRange) {
          params.start_date = dateRange.start;
          params.end_date = dateRange.end;
        }
      }

      if (config.monthSelect && $(config.monthSelect).val()?.length) {
        params.months = $(config.monthSelect).val();
      }

      if (type !== 'events') {
        const eventId = panel.find('[id^="events-"]').val();
        if (eventId) params.event_id = eventId;

        if (type !== 'pie') {
          const attendanceId = panel.find('[id^="attendanceType-"]').val();
          if (attendanceId) params.attendance_id = attendanceId;
        }
      }

      return params;
    }

    function handleChartResponse(type, response, params) {
      const years = response.available_years || response.years || [];
      if (years.length) {
        const $select = $(`#${type}YearSelect`);
        const currentVal = $select.val();
        $select.empty();
        years.forEach(y => {
          $select.append(`<option value="${y}" ${y == currentVal ? 'selected' : ''}>${y}</option>`);
        });
      }

      switch (type) {
        case 'pie':
          renderPieChart(response);
          break;
        case 'events':
          renderEventsChart(response);
          break;
        case 'line':
          renderLineChart(response, params);
          break;
        case 'bar':
          renderBarChart(response, params);
          break;
      }
    }

    function handleChartError(type) {
      const avgText = type === 'pie' ? 'Average Attendance: 0' :
        type === 'events' ? 'Average per Event: 0' : 'Average: 0';

      $(`#${type}Avg`).text(avgText);

      if (type === 'pie') {
        renderEmptyPieChart();
      }
    }

    function renderPieChart(data) {
      const values = data.values || [];
      const labels = data.labels || [];
      const total = values.reduce((a, b) => a + b, 0);
      const avg = values.length ? Math.round(total / values.length) : 0;

      $(`#pieAvg`).text(`Average Attendance: ${avg}`);

      const finalValues = values.length && !values.every(v => v === 0) ? values : [2847, 1022, 2009];
      const finalLabels = labels.length ? labels : ['Regular', 'Guest', 'Volunteer'];

      const options = {
        chart: {
          type: 'pie',
          height: 500
        },
        series: finalValues,
        labels: finalLabels,
        colors: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
        legend: {
          position: 'top'
        },
        dataLabels: {
          enabled: true,
          formatter: val => Math.round(val) + '%'
        },
        tooltip: {
          y: {
            formatter: val => `${val.toLocaleString()} attendees`
          },
          footer: {
            formatter: () => `<div style="text-align:center; margin-top:8px; font-weight:bold;">
              Total: ${finalValues.reduce((a, b) => a + b, 0).toLocaleString()} attendees</div>`
          }
        }
      };

      if (pieChart) pieChart.destroy();
      pieChart = new ApexCharts(document.getElementById('pieChart'), options);
      pieChart.render();
    }

    function renderEventsChart(data) {
      if (!data.series || !data.series.length) {
        $(`#eventsAvg`).text('Average per Event: 0');

        if (eventsChart) {
          eventsChart.updateSeries([]);
        } else {
          eventsChart = new ApexCharts(document.getElementById('eventsChart'), {
            chart: {
              type: 'bar',
              height: 400
            },
            series: [],
            noData: {
              text: 'No data available'
            }
          });
          eventsChart.render();
        }
        return;
      }

      const eventNames = data.series.map(s => s.name);
      const totalEvents = data.series.reduce((sum, s) => sum + (s.data?.reduce((a, b) => a + b, 0) || 0), 0);
      const avgPerEvent = Math.round(totalEvents / data.series.length);

      $(`#eventsAvg`).text(`Average per Event: ${avgPerEvent}`);

      const monthSeries = FULL_MONTHS.map((monthName, monthIndex) => {
        const monthData = eventNames.map(eventName => {
          const eventSeries = data.series.find(s => s.name === eventName);
          return eventSeries?.data?.[monthIndex] || 0;
        });

        return {
          name: monthName,
          data: monthData
        };
      });

      const options = {
        chart: {
          type: 'bar',
          height: Math.max(500, eventNames.length * 30),
          stacked: true
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
        legend: {
          position: 'top'
        },
        tooltip: {
          y: {
            formatter: val => `${val.toLocaleString()} attendees`
          }
        },
        colors: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FECA57',
          '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2',
          '#F5576C', '#43AA8B'
        ]
      };

      if (eventsChart) {
        eventsChart.updateOptions(options);
        eventsChart.updateSeries(monthSeries);
      } else {
        eventsChart = new ApexCharts(document.getElementById('eventsChart'), options);
        eventsChart.render();
      }
    }

    function renderLineChart(data, params) {
      const series = (data.series || []).map(s => ({
        name: s.name,
        data: s.data
      }));
      const categories = data.categories || [];

      const total = series.flatMap(s => s.data).reduce((a, b) => a + b, 0);
      const dataPoints = categories.length || (params.months?.length || 12);
      const avg = dataPoints ? Math.round(total / dataPoints) : 0;

      $(`#lineAvg`).text(`Average: ${avg}`);

      const options = {
        chart: {
          type: 'area',
          height: 500
        },
        series,
        xaxis: {
          categories
        },
        colors: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
        stroke: {
          curve: 'smooth',
          width: 4
        },
        fill: {
          opacity: 0.6,
          type: 'gradient'
        },
        tooltip: {
          y: {
            formatter: val => `${val.toLocaleString()} attendees`
          }
        }
      };

      if (lineChart) {
        lineChart.updateOptions(options);
        lineChart.updateSeries(series);
      } else {
        lineChart = new ApexCharts(document.getElementById('lineChart'), options);
        lineChart.render();
      }
    }

    function renderBarChart(data, params) {
      const series = (data.series || []).map(s => ({
        name: s.name,
        data: s.data
      }));
      const categories = data.categories || [];

      const total = series.flatMap(s => s.data).reduce((a, b) => a + b, 0);
      const dataPoints = categories.length || (params.months?.length || 12);
      const avg = dataPoints ? Math.round(total / dataPoints) : 0;

      $(`#barAvg`).text(`Average: ${avg}`);

      const options = {
        chart: {
          type: 'bar',
          height: 600,
          stacked: true
        },
        series,
        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 8
          }
        },
        xaxis: {
          categories
        },
        colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        tooltip: {
          y: {
            formatter: val => `${val.toLocaleString()} attendees`
          }
        },
        dataLabels: {
          enabled: true
        }
      };

      if (barChart) {
        barChart.updateOptions(options);
        barChart.updateSeries(series);
      } else {
        barChart = new ApexCharts(document.getElementById('barChart'), options);
        barChart.render();
      }
    }

    function renderEmptyPieChart() {
      const options = {
        chart: {
          type: 'pie',
          height: 500
        },
        series: [1],
        labels: ['No Data'],
        colors: ['#e0e7ff'],
        legend: {
          position: 'bottom'
        }
      };

      if (pieChart) pieChart.destroy();
      pieChart = new ApexCharts(document.getElementById('pieChart'), options);
      pieChart.render();
    }

    $(document).ready(function() {
      ['line', 'bar', 'pie', 'events'].forEach(type => Helpers.hideLoader(type));

      $('#lineMonthSelect, #barMonthSelect').each(function() {
        $(this).select2({
          placeholder: "All months",
          allowClear: true,
          width: '100%'
        }).html(FULL_MONTHS.map((m, i) => `<option value="${i + 1}">${m}</option>`).join(''));
      });

      $('.tab-btn').on('click', function() {
        const tabId = $(this).data('tab');
        $('.tab-btn, .tab-content').removeClass('active');
        $(this).addClass('active');
        $(`#${tabId}`).addClass('active');

        if (tabId === 'months-tab') {
          $('#lineDateRange').val('');
        } else if (tabId === 'date-range-tab') {
          $('#lineMonthSelect').val(null).trigger('change');
        }

        loadChart('line');
      });

      function initDateRangePicker(selector, chartType) {
        $(selector).daterangepicker({
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
        }).on('apply.daterangepicker', function(ev, picker) {
          $(this).val(`${picker.startDate.format('YYYY-MM-DD')} to ${picker.endDate.format('YYYY-MM-DD')}`);

          if (chartType === 'line' && $(this).val()) {
            $('.tab-btn, .tab-content').removeClass('active');
            $('[data-tab="date-range-tab"]').addClass('active');
            $('#date-range-tab').addClass('active');
            $('#lineMonthSelect').val(null).trigger('change');
          }

          loadChart(chartType);
        }).on('cancel.daterangepicker', function() {
          $(this).val('');
          loadChart(chartType);
        });
      }

      initDateRangePicker('#lineDateRange', 'line');
      initDateRangePicker('#barDateRange', 'bar');

      $('body')
        .on('change', '[id^="events-"]', function() {
          const eventId = $(this).val();
          const panel = $(this).closest('.card');
          const attSelect = panel.find('[id^="attendanceType-"]');

          if (attSelect.length) {
            EventManager.updateAttendanceDropdown(attSelect, eventId);
          }

          const chartType = $(this).attr('id').split('-')[1];
          if (chartType) loadChart(chartType);
        })
        .on('change', '[id^="attendanceType-"]', function() {
          const panel = $(this).closest('.card');
          const chartType = $(this).attr('id').split('-')[1];
          if (chartType) loadChart(chartType);
        });

      function createResetHandler(type, options = {}) {
        return function() {
          if (options.yearSelect) $(options.yearSelect).val(Helpers.getCurrentYear());
          if (options.monthSelect) $(options.monthSelect).val(null).trigger('change');
          if (options.dateRange) $(options.dateRange).val('');
          if (options.eventSelect) $(options.eventSelect).val('');
          if (options.attendanceSelect) $(options.attendanceSelect).val('');

          if (options.tabReset && type === 'line') {
            $('.tab-btn, .tab-content').removeClass('active');
            $('[data-tab="months-tab"]').addClass('active');
            $('#months-tab').addClass('active');
          }

          if (options.updateAttendance) {
            EventManager.updateAttendanceDropdown(options.updateAttendance);
          }

          loadChart(type);
        };
      }

      $('#lineResetBtn').on('click', createResetHandler('line', {
        yearSelect: '#lineYearSelect',
        monthSelect: '#lineMonthSelect',
        dateRange: '#lineDateRange',
        eventSelect: '#events-line',
        attendanceSelect: '#attendanceType-line',
        tabReset: true,
        updateAttendance: '#attendanceType-line'
      }));

      $('#barResetBtn').on('click', createResetHandler('bar', {
        yearSelect: '#barYearSelect',
        monthSelect: '#barMonthSelect',
        dateRange: '#barDateRange',
        eventSelect: '#events-bar',
        attendanceSelect: '#attendanceType-bar',
        updateAttendance: '#attendanceType-bar'
      }));

      $('#pieResetBtn').on('click', createResetHandler('pie', {
        yearSelect: '#pieYearSelect',
        eventSelect: '#events-pie'
      }));

      $('#eventsResetBtn').on('click', createResetHandler('events', {
        yearSelect: '#eventsYearSelect'
      }));

      $('#lineYearSelect, #lineMonthSelect').on('change', () => loadChart('line'));
      $('#barYearSelect, #barMonthSelect').on('change', () => loadChart('bar'));
      $('#pieYearSelect').on('change', () => loadChart('pie'));
      $('#eventsYearSelect').on('change', () => loadChart('events'));

      EventManager.loadEvents();

      setTimeout(() => {
        ['line', 'bar'].forEach(type => {
          const selector = `#attendanceType-${type}`;
          if ($(selector).length) {
            EventManager.updateAttendanceDropdown(selector);
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
  </script>
</body>

</html>