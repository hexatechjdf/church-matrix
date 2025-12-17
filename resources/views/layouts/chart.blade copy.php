<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Church Metric Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #858794 0%, #7f7e80 100%);
            min-height: 100vh;
            padding: 30px 20px;
            color: #333;
        }

        .dashboard {
            max-width: 1300px;
            margin: 0 auto;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-group {
            position: relative;
        }

        .filter-group select {
            width: 100%;
            padding: 14px 40px 14px 16px;
            border: none;
            border-radius: 12px;
            background: white;
            font-size: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            appearance: none;
            cursor: pointer;
        }

        .filter-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            pointer-events: none;
        }

        .week-picker {
            position: relative;
        }

        .week-trigger {
            width: 100%;
            padding: 14px 16px;
            border: none;
            border-radius: 12px;
            background: white;
            font-size: 15px;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .week-trigger:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .week-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 8px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.22);
            overflow: hidden;
            z-index: 1000;
            display: none;
            flex-direction: row;
        }

        .week-dropdown.open {
            display: flex;
        }

        .week-buttons {
            background: #f8f9fa;
            padding: 16px 8px;
            width: 140px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .week-btn {
            padding: 10px 12px;
            border: none;
            background: #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        .week-btn:hover {
            background: #dee2e6;
        }

        .week-btn.active {
            background: #667eea;
            color: white;
        }

        .mini-calendar {
            padding: 4px;
            min-width: 231px;
        }

        .cal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }

        .cal-month {
            font-size: 15px;
        }

        .cal-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
        }

        .cal-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0px;
            text-align: center;
        }

        .cal-day {
            width: 32px;
            height: 32px;
            line-height: 32px;
            border-radius: 50%;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
        }

        .cal-day:hover {
            background: #e9ecef;
        }

        .cal-day.today {
            background: #28a745;
            color: white;
            font-weight: bold;
        }

        .cal-day.in-week {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: bold;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #667eea);
            background-size: 200%;
            animation: gradientShift 4s ease infinite;
        }

        .chart-wrapper {
            position: relative;
            height: 420px;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%
            }

            50% {
                background-position: 100% 50%
            }
        }
    </style>
</head>

<body>
@yield('content')
  <script>
        let currentDate = new Date();
        const chartCtx = document.getElementById('myChart').getContext('2d');
        const chart = new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

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

            chart.data.labels = result.labels;
            chart.data.datasets = result.datasets;
            chart.update();
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
        updateWeekDisplay();
        loadChartData();
        
    </script>

</body>

</html>