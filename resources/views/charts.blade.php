<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ApexCharts — Line Chart with Year filter & Select2 Month multi-select</title>

  <!-- ApexCharts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>

  <!-- jQuery (required by Select2) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Basic styles -->
  <style>
    body { font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; margin: 18px; background: #fafafa; color: #222 }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 6px 20px rgba(30,30,30,0.06); padding: 18px; margin-bottom: 18px }
    .controls { display:flex; gap:12px; flex-wrap:wrap; align-items:center }
    label { font-size: 13px; margin-right:8px }
    select, .select2-container { min-width: 180px }
    #chart { max-width: 100%; }
    .muted { color: #666; font-size:13px }
    @media (max-width:640px){ .controls{flex-direction:column;align-items:stretch} }
  </style>
</head>
<body>

  <h2>ApexCharts — Multi-series Line Chart with Year filter & month Select2 multi-select</h2>
  <p class="muted">Choose a year, pick months (multi-select). Chart updates immediately.</p>

  <div class="card">
    <div class="controls">
      <div>
        <label for="yearSelect">Year</label><br />
        <select id="yearSelect"></select>
      </div>

      <div>
        <label for="monthSelect">Months (multi)</label><br />
        <select id="monthSelect" multiple="multiple"></select>
      </div>

      <div>
        <label>&nbsp;</label><br />
        <button id="resetBtn">Reset</button>
      </div>

    </div>
  </div>

  <div class="card">
    <div id="chart"></div>
  </div>

  <script>
  // ---------------------------
  // Sample data structure
  // ---------------------------
  // We provide multiple series (datasets) across several years. Each series has 12 monthly values.
  const monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

  // Example years (you can add more). Values are sample numbers — replace with your real data.
  const rawData = {
    2023: {
      "Sales":    [120, 150, 170, 140, 180, 200, 220, 210, 190, 230, 250, 270],
      "Profit":   [25, 40, 30, 45, 50, 60, 55, 70, 65, 80, 90, 95],
      "Customers":[800, 920, 1000, 950, 1100, 1200, 1300, 1250, 1180, 1400, 1500, 1600]
    },
    2024: {
      "Sales":    [140, 160, 190, 180, 200, 220, 240, 235, 210, 260, 280, 300],
      "Profit":   [30, 45, 50, 55, 65, 75, 70, 80, 75, 95, 105, 110],
      "Customers":[900, 1000, 1100, 1080, 1250, 1350, 1450, 1400, 1330, 1550, 1650, 1750]
    },
    2025: {
      "Sales":    [150, 170, 210, 200, 230, 250, 270, 265, 240, 290, 310, 330],
      "Profit":   [35, 55, 60, 65, 80, 90, 85, 95, 90, 110, 120, 130],
      "Customers":[950, 1050, 1200, 1150, 1350, 1450, 1550, 1500, 1450, 1700, 1800, 1900]
    }
  };

  // Series styles: you can add more series here with custom dash/marker styles.
  const seriesStyles = {
    "Sales": { strokeDashArray: 0, markerSize: 4 },
    "Profit": { strokeDashArray: 6, markerSize: 3 },
    "Customers": { strokeDashArray: 0, markerSize: 2 }
  };

  // ---------------------------
  // Populate Year and Month controls
  // ---------------------------
  const yearSelect = document.getElementById('yearSelect');
  const monthSelect = $('#monthSelect'); // using jQuery for Select2

  const years = Object.keys(rawData).sort();
  years.forEach(y => {
    const opt = document.createElement('option'); opt.value = y; opt.textContent = y; yearSelect.appendChild(opt);
  });

  // Populate months into Select2
  monthNames.forEach((m, idx) => {
    const opt = new Option(m, idx);
    monthSelect.append(opt);
  });

  // Initialize Select2
  monthSelect.select2({
    placeholder: 'Select months',
    width: '220px'
  });

  // Defaults: choose the first year (latest) and pre-select all months
  yearSelect.value = years[years.length-1];
  const allMonthIndexes = monthNames.map((_,i)=>i.toString());
  monthSelect.val(allMonthIndexes).trigger('change');

  // ---------------------------
  // ApexCharts initial config
  // ---------------------------
  let chart = null;

  function buildSeriesForYear(year, selectedMonthIndexes){
    const yearData = rawData[year];
    const categories = selectedMonthIndexes.map(i => monthNames[Number(i)]);

    const series = Object.keys(yearData).map(seriesName => {
      // extract only values for selected months (indices)
      const vals = selectedMonthIndexes.map(i => yearData[seriesName][Number(i)]);
      const s = {
        name: seriesName,
        data: vals
      };
      // apply style hints
      if(seriesStyles[seriesName]){
        s.strokeDashArray = seriesStyles[seriesName].strokeDashArray;
        s.marker = { size: seriesStyles[seriesName].markerSize };
      }
      return s;
    });

    return { series, categories };
  }

  function createOrUpdateChart(year, selectedMonthIndexes){
    const { series, categories } = buildSeriesForYear(year, selectedMonthIndexes);

    const options = {
      chart: {
        id: 'multi-line',
        type: 'line',
        height: 420,
        toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } }
      },
      stroke: { width: 2, curve: 'smooth' },
      markers: { hover: { size: 6 } },
      series: series,
      xaxis: {
        categories: categories,
        title: { text: 'Month' }
      },
      yaxis: [
        { title: { text: 'Value' } }
      ],
      legend: { position: 'top' },
      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: function(val){ return val; }
        }
      },
      grid: { borderColor: '#eee' },
      responsive: [{
        breakpoint: 640,
        options: { chart: { height: 360 }, legend: { position: 'bottom' } }
      }]
    };

    if(chart){
      chart.updateOptions({ xaxis: { categories }, series }, true, true);
    } else {
      chart = new ApexCharts(document.querySelector('#chart'), options);
      chart.render();
    }
  }

  // ---------------------------
  // Wire up controls
  // ---------------------------
  function refreshFromControls(){
    const year = yearSelect.value;
    const selectedMonths = $('#monthSelect').val() || [];
    // If no months selected, show all months
    const monthsToUse = selectedMonths.length ? selectedMonths : allMonthIndexes;
    createOrUpdateChart(year, monthsToUse);
  }

  // listeners
  yearSelect.addEventListener('change', refreshFromControls);
  $('#monthSelect').on('change', refreshFromControls);

  document.getElementById('resetBtn').addEventListener('click', () => {
    // reset to latest year and all months selected
    yearSelect.value = years[years.length-1];
    monthSelect.val(allMonthIndexes).trigger('change');
    refreshFromControls();
  });

  // initial render
  refreshFromControls();

  </script>
  <!-- Additional Charts -->
  <div class="card"><h3>Pie Chart – All Events</h3><div id="pieAll"></div></div>
  <div class="card"><h3>Stacked Bar – Events as Series</h3><div id="barStacked"></div></div>
  <div class="card"><h3>Grouped Bar – Same Filters</h3><div id="barGrouped"></div></div>
  <div class="card"><h3>Grouped + Stacked – Whole Year</h3><div id="barGroupStack"></div></div>
  <div class="card"><h3>Donut – Event Data by Month</h3><div id="donutMonth"></div></div>

<script>
// Build Aggregated Data Helpers
function buildTotals(year){
  const d = rawData[year];
  return Object.keys(d).map(k=>({name:k, total:d[k].reduce((a,b)=>a+b,0)}));
}

function createPie(year){
  const totals = buildTotals(year);
  new ApexCharts(document.querySelector('#pieAll'),{
    chart:{type:'pie'},
    series: totals.map(t=>t.total),
    labels: totals.map(t=>t.name)
  }).render();
}

function createStackedBar(year){
  const d = rawData[year];
  new ApexCharts(document.querySelector('#barStacked'),{
    chart:{type:'bar', stacked:true},
    series: Object.keys(d).map(k=>({name:k,data:d[k]})),
    xaxis:{categories:monthNames}
  }).render();
}

function createGroupedBar(year){
  const d = rawData[year];
  new ApexCharts(document.querySelector('#barGrouped'),{
    chart:{type:'bar'},
    plotOptions:{bar:{horizontal:false}},
    series: Object.keys(d).map(k=>({name:k,data:d[k]})),
    xaxis:{categories:monthNames}
  }).render();
}

function createGroupStack(year){
  const d = rawData[year];
  new ApexCharts(document.querySelector('#barGroupStack'),{
    chart:{type:'bar', stacked:true},
    series: Object.keys(d).map(k=>({name:k,data:d[k]})),
    xaxis:{categories:monthNames}
  }).render();
}

function createDonut(year, monthIndex){
  const d = rawData[year];
  new ApexCharts(document.querySelector('#donutMonth'),{
    chart:{type:'donut'},
    series: Object.keys(d).map(k=>d[k][monthIndex]),
    labels: Object.keys(d)
  }).render();
}

// Init all charts once
const defaultYear = years[years.length-1];
createPie(defaultYear);
createStackedBar(defaultYear);
createGroupedBar(defaultYear);
createGroupStack(defaultYear);
createDonut(defaultYear,0);
</script>
</body>
</html>
