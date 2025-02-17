// WEEKLY TRADES CHART (DASHBOARD)
document.addEventListener('DOMContentLoaded', function () {
    const options2 = {
        chart: {
            height: "70%",
            maxWidth: "100%",
            type: "area",
            fontFamily: "Inter, sans-serif",
            dropShadow: {
                enabled: false,
            },
            toolbar: {
                show: false,
            },
        },
        tooltip: {
            enabled: false,
            x: {
                show: false,
            },
        },
        fill: {
            type: "gradient",
            gradient: {
                opacityFrom: 0.55,
                opacityTo: 0,
                shade: "maroon",
                gradientToColors: ["maroon"],
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: 6,
        },
        grid: {
            show: false,
            strokeDashArray: 4,
            padding: {
                left: 2,
                right: 2,
                top: 0
            },
        },
        series: [
            {
                name: "Trades",
                data: [0,0,0,0,0],
                color: "red",
            },
        ],
        xaxis: {
            categories: ['01 February', '02 February', '03 February', '04 February', '05 February', '06 February', '07 February'],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            show: false,
        },
    };

    try {
        const chartElement = document.getElementById("area-chart1");
        if (chartElement && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(chartElement, options2);
            chart.render();
        } else {
            console.error('Chart element not found or ApexCharts not loaded');
        }
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
});


// TRADE STATISTICS CHART (DASHBOARD)
const getChartOptions = () => {
    return {
      series: [1, 0, 0],
      colors: ["maroon", "red", "grey"],
      chart: {
        height: 250,
        width: "100%",
        type: "pie",
      },
      stroke: {
        colors: ["white"],
        lineCap: "",
      },
      plotOptions: {
        pie: {
          labels: {
            show: false,
          },
          size: "100%",
          dataLabels: {
            offset: -25
          }
        },
      },
      labels: ["Success", "Failed", "Undefined"],
      dataLabels: {
        enabled: true,
        style: {
          fontFamily: "Inter, sans-serif",
        },
      },
      legend: {
        position: "bottom",
        fontFamily: "Inter, sans-serif",
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return value + "%"
          },
        },
      },
      xaxis: {
        labels: {
          formatter: function (value) {
            return value  + "%"
          },
        },
        axisTicks: {
          show: false,
        },
        axisBorder: {
          show: false,
        },
      },
    }
  }
  
  if (document.getElementById("pie-chart") && typeof ApexCharts !== 'undefined') {
    const chart = new ApexCharts(document.getElementById("pie-chart"), getChartOptions());
    chart.render();
  }
  


// REVENUE STREAM CHART (SALES)
document.addEventListener('DOMContentLoaded', function () {
    const options = {
        chart: {
            height: "60%",
            maxWidth: "100%",
            type: "area",
            fontFamily: "Inter, sans-serif",
            dropShadow: {
                enabled: false,
            },
            toolbar: {
                show: false,
            },
        },
        tooltip: {
            enabled: false,
            x: {
                show: false,
            },
        },
        fill: {
            type: "gradient",
            gradient: {
                opacityFrom: 0.55,
                opacityTo: 0,
                shade: "#maroon",
                gradientToColors: ["maroon"],
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: 6,
        },
        grid: {
            show: false,
            strokeDashArray: 4,
            padding: {
                left: 2,
                right: 2,
                top: 0
            },
        },
        series: [
            {
                name: "revenue",
                data: [6500, 6418, 6456, 6526, 6356, 6456],
                color: "red",
            },
        ],
        xaxis: {
            categories: ['01 February', '02 February', '03 February', '04 February', '05 February', '06 February', '07 February'],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            show: false,
        },
    };

    try {
        const chartElement = document.getElementById("area-chart");
        if (chartElement && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(chartElement, options);
            chart.render();
        } else {
            console.error('Chart element not found or ApexCharts not loaded');
        }
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
});



// TOTAL SALES CHART (SALES)
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    const options = {
        // add data series via arrays, learn more here: https://apexcharts.com/docs/series/
        series: [{
            name: "Sellers",
            data: [1500, 1418, 1456, 1526, 1356, 1256],
            color: "maroon",
        },
        {
            name: "Buyers",
            data: [643, 413, 765, 412, 1423, 1731],
            color: "red",
        },
        ],
        chart: {
            height: "100%",
            maxWidth: "100%",
            type: "area",
            fontFamily: "Inter, sans-serif",
            dropShadow: {
                enabled: false,
            },
            toolbar: {
                show: false,
            },
        },
        tooltip: {
            enabled: true,
            x: {
                show: false,
            },
        },
        legend: {
            show: true
        },
        fill: {
            type: "gradient",
            gradient: {
                opacityFrom: 0.55,
                opacityTo: 0,
                shade: "maroon",
                gradientToColors: ["maroon"],
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: 6,
        },
        grid: {
            show: false,
            strokeDashArray: 4,
            padding: {
                left: 2,
                right: 2,
                top: -26
            },
        },
        xaxis: {
            categories: ['01 February', '02 February', '03 February', '04 February', '05 February', '06 February',
                '07 February'
            ],
            labels: {
                show: true,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            show: true,
            labels: {
                formatter: function (value) {
                    return '₱' + value;
                }
            }
        },
    };

    try {
        const chartElement = document.getElementById("legend-chart");
        if (chartElement && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(chartElement, options);
            chart.render();
        } else {
            console.error('Chart element not found or ApexCharts not loaded');
        }
    } catch (error) {
        console.error('Error initializing chart:', error);
    }
});