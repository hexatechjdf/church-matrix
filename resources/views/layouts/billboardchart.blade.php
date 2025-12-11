<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Billboard.js Chart Example</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/billboard.js/dist/billboard.min.css">
  <style>
    #chart {
      max-width: 600px;
      margin: 50px auto;
    }
  </style>
</head>
<body>
  <div id="chart"></div>

  <script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
  <script src="https://cdn.jsdelivr.net/npm/billboard.js/dist/billboard.min.js"></script>
  <script>
    const chart = bb.generate({
      data: {
        
        json: [
			{name: "www.site1.com", upload1: 800, download: 500, total: 400},
			{name: "www.site2.com", upload: 600, download: 600, total: 400},
			{name: "www.site3.com", upload: 400, download: 800, total: 500},
			{name: "www.site4.com", upload: 400, download: 700, total: 500}
		],
		keys: {
x: "name",
			value: ["upload", "download","upload1"]
		},
groups: [
     ["upload", "download","upload1"]
    ],
        type: "bar" // smooth line
        
      },
  tooltip: {
    contents: function (data, defaultTitleFormat, defaultValueFormat, color) {
      

      // find original json row
      data  =data.filter(t=>t.value!=null);

let row = chart.config().data.json[data[0].x].name;
console.log(data,row);
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
  </script>
</body>
</html>
