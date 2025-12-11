<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stacked Horizontal Bar - Tooltip All Series</title>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; }
  #chart { max-width: 800px; margin: 0 auto; }
</style>
</head>
<body>

<h2 style="text-align:center;">Fiction Books Sales - Tooltip Showing All Series</h2>
<div id="chart"></div>

<script>
var seriesData = [
  { name: 'Marine Sprite', data: [44, 55, 41, 37, 22, 43, 21] },
  { name: 'Striking Calf', data: [53, 32, 33, 52, 13, 43, 32] },
  { name: 'Tank Picture', data: [12, 17, 11, 9, 15, 11, 20] },
  { name: 'Bucket Slope', data: [9, 7, 5, 8, 6, 9, 4] },
  { name: 'Reborn Kid', data: [25, 12, 19, 32, 25, 24, 10] }
];

var categories = [2008, 2009, 2010, 2011, 2012, 2013, 2014];

// Compute totals per x-axis
var totals = categories.map((_, i) => seriesData.reduce((sum, s) => sum + s.data[i], 0));

var options = {
  series: seriesData,
  chart: { type: 'bar', height: 350, stacked: true },
  plotOptions: { bar: { horizontal: true } },
  stroke: { width: 1, colors: ['#fff'] },
  xaxis: { categories: categories },
  fill: { opacity: 1 },
  legend: { position: 'top', horizontalAlign: 'left', offsetX: 40 },
  tooltip: {
    shared: true,
intersect:false,
    custom: function({ series, dataPointIndex }) {
      let html = `<div style="padding:10px; min-width:150px"><strong>Year: ${categories[dataPointIndex]}</strong><br>`;
console.log(dataPointIndex,series);
      series.forEach((val, idx) => {
        html += `${seriesData[idx].name}: ${seriesData[idx].data[dataPointIndex]}<br>`;
      });
      html += `<strong>Total: ${totals[dataPointIndex]}</strong></div>`;
      return html;
    }
  }
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();
</script>

</body>
</html>
