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
    <script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
    <script src="https://cdn.jsdelivr.net/npm/billboard.js/dist/billboard.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

    <script>
        let currentDate = new Date();
        // const chartCtx = document.getElementById('myChart').getContext('2d');
        // const chart = new Chart(chartCtx, {
        //     type: 'bar',
        //     data: {
        //         labels: [],
        //         datasets: []
        //     },
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         plugins: {
        //             legend: {
        //                 position: 'top'
        //             },
        //             tooltip: {
        //                 mode: 'index',
        //                 intersect: false
        //             }
        //         },
        //         scales: {
        //             y: {
        //                 beginAtZero: true
        //             }
        //         }
        //     }
        // });

        function getWeekStart(d) {
            const date = new Date(d);
            const day = date.getDay();
            date.setDate(date.getDate() - day);
            return date;
        }

        function getWeekEnd(d) {
            const date = new Date(d);
            const day = date.getDay();
            date.setDate(date.getDate() + (6 - day));
            return date;
        }

        function updateSelectedWeek() {
            const start = getWeekStart(currentDate);
            const end = getWeekEnd(currentDate);
            window.selectedWeekStart = start.toISOString().split('T')[0];
            window.selectedWeekEnd = end.toISOString().split('T')[0];
            loadChartData();
        }

        async function loadChartData() {
            const filters = {
                campus: document.getElementById("campusFilter").value,
                event: document.getElementById("eventFilter").value,
                week_start: window.selectedWeekStart ?? "",
                week_end: window.selectedWeekEnd ?? ""
            };
            const params = new URLSearchParams(filters).toString();

            const res = await fetch(`/charts/data?${params}`);
            const result = await res.json();
            const allValues = _(result.json)
                .groupBy("month_year")
                .map((items, month) => {
                    const base = {
                        month_year: month,
                        first_created_date: items[0].first_created_date
                    };

                    // Add dynamic keys
                    items.forEach(i => {
                        base[i.attendance_id] = i.attendance_count;
                    });

                    return base;
                })
                .value();

            console.log(result);
            const chart = bb.generate({
                size: {},
                data: {

                    json: allValues,
                    keys: {
                        x: result.keys.name,
                        value: result.keys.values
                    },
                    groups: [
                        result.keys.values
                    ],
                    type: "bar" // smooth line

                },
                tooltip: {
                    contents: function(data, defaultTitleFormat, defaultValueFormat, color) {

                        console.log(data);
                        // find original json row
                        data = data.filter(t => t.value != null);

                        let row = chart.config().data.json[data[0].x][result.keys.name];

                        const total = data.reduce((sum, val) => sum + val.value, 0);
                        let html = `<table class="bb-tooltip"><tbody>`;
                        html += `<tr><th colspan="2">${row}</th></tr>`;

                        // Show normal dataset values
                        data.forEach(d => {
                            html += `
          <tr>
            <td style="color:${color(d)}">${d.id}</td>
            <td>${d.value}</td>
          </tr>
        `;
                        });

                        // Add custom TOTAL row
                        html += `
        <tr style="font-weight:bold;">
          <td>Total</td>
          <td>${total}</td>
        </tr>
      `;

                        html += `</tbody></table>`;
                        return html;
                    }
                },
                axis: {
                    x: {
                        type: "category"
                    }
                },
                point: {
                    r: 4 // size of points
                },
                bindto: "#chart"
            });

            // chart.data.labels = result.labels;
            // chart.data.datasets = result.datasets;
            // chart.update();
        }

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            let html = `<div class="cal-header"><span class="cal-month">${monthNames[month]} ${year}</span></div>`;
            html += `<div class="cal-weekdays">
                <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
            </div>`;
            html += '<div class="cal-days">';
            for (let i = 0; i < firstDay; i++) html += '<div></div>';
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const today = new Date();
                let classes = 'cal-day';
                if (date.toDateString() === today.toDateString()) classes += ' today';
                if (date >= getWeekStart(currentDate) && date <= getWeekEnd(currentDate)) classes += ' in-week';
                html += `<div class="${classes}" onclick="selectDate(${year},${month},${day})">${day}</div>`;
            }
            html += '</div>';
            document.getElementById('miniCalendar').innerHTML = html;
        }

        function updateWeekDisplay() {
            const start = getWeekStart(currentDate);
            document.getElementById('weekText').textContent = `Week of ${start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
            renderCalendar();
            document.querySelectorAll('.week-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`.week-btn[data-week="0"]`).classList.add('active');
            updateSelectedWeek();
        }

        window.selectDate = function(y, m, d) {
            currentDate = new Date(y, m, d);
            updateWeekDisplay();
            document.getElementById('weekDropdown').classList.remove('open');
        };

        document.querySelectorAll('.week-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const offset = parseInt(btn.dataset.week) * 7;
                currentDate = new Date();
                currentDate.setDate(currentDate.getDate() + offset);
                updateWeekDisplay();
                document.getElementById('weekDropdown').classList.remove('open');
            });
        });

        document.getElementById('weekTrigger').addEventListener('click', e => {
            e.stopPropagation();
            document.getElementById('weekDropdown').classList.toggle('open');
            renderCalendar();
        });

        document.addEventListener('click', () => {
            document.getElementById('weekDropdown').classList.remove('open');
        });

        document.getElementById('campusFilter').addEventListener('change', loadChartData);
        document.getElementById('eventFilter').addEventListener('change', loadChartData);

        // Initial render

        loadChartData();
    </script>

</body>

</html>
