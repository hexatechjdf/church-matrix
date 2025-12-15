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

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
      min-height: 100vh;
      padding: 30px 20px;
      color: #1e293b;
    }
    .container { max-width: 1400px; margin: 0 auto; }

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
      box-shadow: 0 20px 50px rgba(0,0,0,0.1);
      padding: 28px;
      margin-bottom: 28px;
      border: 1px solid rgba(139,92,246,0.1);
    }

    .controls {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      align-items: end;
    }

    label {
      font-weight: 600;
      color: #4c1d95;
      font-size: 0.95rem;
      margin-bottom: 8px;
      display: block;
    }

    select, button {
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    #yearSelect {
      border: 2px solid #e0e7ff;
      background: white;
    }
    #yearSelect:focus {
      border-color: #8b5cf6;
      box-shadow: 0 0 0 4px rgba(139,92,246,0.25);
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
      box-shadow: 0 0 0 4px rgba(139,92,246,0.2) !important;
    }

    .select2-selection__choice {
      background: linear-gradient(135deg, #8b5cf6, #a78bfa) !important;
      color: white !important;
      border-radius: 12px !important;
      padding: 6px 12px !important;
      font-weight: 600 !important;
    }

    #resetBtn {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      color: white;
      border: none;
      font-weight: 600;
      cursor: pointer;
      height: 52px;
    }
    #resetBtn:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(139,92,246,0.4);
    }

    @media (max-width: 768px) {
      .controls { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="container">

  <div class="card">
     <div class="card">
    <div class="controls">
      <div>
        <label for="yearSelect">Year</label>
        <select id="yearSelect"></select>
      </div>

      <div>
        <label for="monthSelect">Months</label>
        <select id="monthSelect" multiple></select>
      </div>

      <!-- <div>
        <label>&nbsp;</label>
        <button id="resetBtn">Reset Filters</button>
      </div> -->
    </div>
  </div>
    <div id="chart"></div>
  </div>
</div>

<script>
  const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];

  let chart = null;

  function loadLiveData() {
    const year = $('#yearSelect').val() || new Date().getFullYear();
    const months = $('#monthSelect').val();

    $.get('/apex-chart-data', { year: year, months: months })
      .done(function(res) {
        const $yearSelect = $('#yearSelect').empty();
        res.available_years.forEach(y => {
          $yearSelect.append(`<option value="${y}" ${y == year ? 'selected' : ''}>${y}</option>`);
        });

        const series = res.series.map(item => ({
          name: item.name,
          data: item.data,
          color: {
            "11am Worship": "#8b5cf6",
            "9am Traditional": "#3b82f6",
            "Youth Night": "#10b981",
            "Midweek Service": "#f59e0b"
          }[item.name] || '#' + Math.floor(Math.random()*16777215).toString(16)
        }));

        const options = {
          chart: { type: 'line', height: 520, toolbar: { show: true }, animations: { enabled: true } },
          series: series,
          stroke: { curve: 'smooth', width: 4 },
          markers: { size: 6, hover: { size: 10 } },
          xaxis: { 
            categories: res.categories,
            title: { text: 'Month' }
          },
          yaxis: { 
            title: { text: 'Headcount' },
            labels: { formatter: val => Math.round(val) }
          },
          tooltip: { shared: true, intersect: false },
          legend: { position: 'top', fontSize: '15px', fontWeight: 600 },
          grid: { borderColor: '#e2e8f0', strokeDashArray: 5 },
          title: {
            text: `${year} Attendance by Service`,
            align: 'center',
            style: { fontSize: '20px', fontWeight: 'bold', color: '#4c1d95' }
          },
          fill: { opacity: 0.1, type: 'gradient' }
        };

        if (chart) {
          chart.updateOptions({ series, xaxis: { categories: res.categories }, title: { text: options.title.text } });
        } else {
          chart = new ApexCharts(document.getElementById('chart'), options);
          chart.render();
        }
      });
  }

  // Initialize Select2
  const $monthSelect = $('#monthSelect').select2({
    placeholder: "All months",
    allowClear: true,
    width: '100%'
  });

  monthNames.forEach((month, i) => {
    $monthSelect.append(new Option(month, i + 1));
  });

  // Events
  $('#yearSelect, #monthSelect').on('change', loadLiveData);
  $('#resetBtn').on('click', () => {
    $('#yearSelect').val(new Date().getFullYear());
    $monthSelect.val(null).trigger('change');
    loadLiveData();
  });

  // First load
  loadLiveData();
</script>

</body>
</html>