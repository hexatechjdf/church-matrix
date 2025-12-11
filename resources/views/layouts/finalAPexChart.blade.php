<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stacked Horizontal Bar - Click to Hide Categories</title>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; }
  #chart { max-width: 800px; margin: 0 auto; }
  .hidden-label { opacity: 0.3; cursor: pointer; }
  .active-label { cursor: pointer; }
</style>
</head>
<body>

<h2 style="text-align:center;">Fiction Books Sales - Click Label to Hide</h2>
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
var hiddenCategories = new Set(); // track hidden categories

var chart = new ApexCharts(document.querySelector("#chart"), {
  series: seriesData,
  chart: { type: 'bar', height: 350, stacked: true },
  plotOptions: { bar: { horizontal: true } },
  stroke: { width: 1, colors: ['#fff'] },
  xaxis: { categories: categories },
  fill: { opacity: 1 },
  legend: { position: 'top', horizontalAlign: 'left', offsetX: 40 },
  tooltip: { shared: true, intersect: false },
});

chart.render().then(() => {
  // After render, add click event to x-axis labels
  const labels = document.querySelectorAll('#chart .apexcharts-xaxis text');
  labels.forEach((label, index) => {
    label.style.cursor = 'pointer';
    label.addEventListener('click', () => {
      const category = categories[index];
      if (hiddenCategories.has(category)) {
        hiddenCategories.delete(category);
        label.style.opacity = 1;
      } else {
        hiddenCategories.add(category);
        label.style.opacity = 0.3;
      }
      updateChart();
    });
  });
});

function updateChart() {
  // For each series, hide data for hidden categories
  let newSeries = seriesData.map(s => ({
    name: s.name,
    data: s.data.map((v, i) => hiddenCategories.has(categories[i]) ? 0 : v)
  }));
  chart.updateSeries(newSeries);
}
</script>

</body>
</html>
