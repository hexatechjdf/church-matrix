<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pie Chart with Custom Tooltip</title>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; }
  #chart { max-width: 500px; margin: 0 auto; }
</style>
</head>
<body>

<h2 style="text-align:center;">Fiction Books Sales - Pie Chart</h2>
<div id="chart"></div>

<script>
var seriesData = [
  { name: 'Marine Sprite', data: [44, 55, 41, 37, 22, 43, 21] },
  { name: 'Striking Calf', data: [53, 32, 33, 52, 13, 43, 32] },
  { name: 'Tank Picture', data: [12, 17, 11, 9, 15, 11, 20] },
  { name: 'Bucket Slope', data: [9, 7, 5, 8, 6, 9, 4] },
  { name: 'Reborn Kid', data: [25, 12, 19, 32, 25, 24, 10] }
];

// For pie chart, we need total per series (sum of all categories)
var pieSeries = seriesData.map(s => s.data.reduce((a, b) => a + b, 0));

// Labels for pie chart = series names
var labels = seriesData.map(s => s.name);

// Optional: additional totals (e.g., average)
var additionalTotals = seriesData.map(s => Math.round(s.data.reduce((a,b) => a+b,0)/s.data.length));

var options = {
  chart: { type: 'pie', height: 400 },
  series: pieSeries,
  labels: labels,
  
  legend: { position: 'bottom' }
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();
</script>

</body>
</html>
