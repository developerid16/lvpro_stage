/*

File: Dashboard Init Js File
*/
console.log('chartData', chartData);
// get colors array from the string
function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");

        if (colors) {
            colors = JSON.parse(colors);
            return colors.map(function (value) {
                var newValue = value.replace(" ", "");
                if (newValue.indexOf(",") === -1) {
                    var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);

                    if (color) {
                        color = color.replace(" ", "");
                        return color;
                    }
                    else return newValue;;
                } else {
                    var val = value.split(',');
                    if (val.length == 2) {
                        var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                        rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                        return rgbaColor;
                    } else {
                        return newValue;
                    }
                }
            });
        }
    }
}

//  subscribe modal

// stacked column chart
var linechartBasicColors = getChartColorsArray("stacked-column-chart");
if (linechartBasicColors) {
    var options = {
        chart: {
            height: 360,
            type: 'bar',
            stacked: false,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },

        plotOptions: {
            bar: {
                dataLabels: {
                    position: 'top', // top, center, bottom
                },
            }
        },

        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return  value.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }, 
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#ef3f3b"]
            }
        },

        series: [
            {
                name: 'Total Redemption',
                data: chartData.week.weekchartredmtion
            }, {
                name: 'Total Complete Redemption',
                data: chartData.week.weekchartcomplatd

            }],
        xaxis: {
            categories: chartData.week.weekchartname,
        },
        yaxis: {
            // categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            labels: {
                // formatter: function (value) {
                //     return "$" + value;
                // }
                formatter: function (value) {
                    return  value.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                },
            },
        },
        colors: linechartBasicColors,
        legend: {
            position: 'bottom',
        },
        fill: {
            opacity: 1
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#stacked-column-chart"),
        options
    );

    chart.render();
}
var linechartBasicColors = getChartColorsArray("stacked-column-chart-sale-week");
if (linechartBasicColors) {
    var options = {
        chart: {
            height: 360,
            type: 'bar',
            stacked: false,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },

        plotOptions: {
            bar: {
                dataLabels: {
                    position: 'top', // top, center, bottom
                },
            }
        },

        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return "$" + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            },
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#ef3f3b"]
            }
        },

        series: [
            {
                name: 'Total Sales',
                data: chartData.week.weekchartsale
            },],
        xaxis: {
            categories: chartData.week.weekchartname,
        },
        yaxis: {
            // categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            labels: {
                // formatter: function (value) {
                //     return "$" + value;
                // }
                formatter: function (value) {
                    return "$" + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
            },
        },
        colors: linechartBasicColors,
        legend: {
            position: 'bottom',
        },
        fill: {
            opacity: 1
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#stacked-column-chart-sale-week"),
        options
    );

    chart.render();
}
var linechartBasicColorsUser = getChartColorsArray("stacked-column-chart-user");
if (linechartBasicColorsUser) {
    var options = {
        chart: {
            height: 360,
            type: 'bar',
            stacked: false,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },

        plotOptions: {
            bar: {
                dataLabels: {
                    position: 'top', // top, center, bottom
                },
            }
        },

        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return  value.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');

            },
            offsetY: -20,
            style: {
                fontSize: '12px',
                colors: ["#ef3f3b"]
            }
        },

        series: [{
            name: 'Total Customer',
            data: chartData.week.weekchartcustomer

        },

        ],
        xaxis: {
            categories: chartData.week.weekchartname,
        },
        yaxis: {
            // categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            // labels: {
            //     // formatter: function (value) {
            //     //     return  value;
            //     // }
            // },
            
            labels: {
                formatter: function (value) {
                    return  value.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');

                },
            },
        },
        colors: linechartBasicColors,
        legend: {
            position: 'bottom',
        },
        fill: {
            opacity: 1
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#stacked-column-chart-user"),
        options
    );

    chart.render();
}
// stacked column chart
console.log('chart.month', chart.month);
var linechartBasicColorsSales = getChartColorsArray("stacked-column-chart-sales");
if (linechartBasicColorsSales) {

    var options = {
        chart: {
            height: 360,
            type: 'bar',
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },

        plotOptions: {
            bar: {
                borderRadius: 0,

                dataLabels: {
                    labels: {
                        formatter: function (value) {
                            return "$" + value;
                        }
                    },
                    position: 'top', // top, center, bottom
                },
            }
        },

        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return "$" + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

            },
            style: {
                colors: ['#50cd89']
            },
        },

        series: [{
            name: 'Total Sales',
            data: chartData.month.monthpurches

        }],
        xaxis: {
            categories: chartData.month.monthnames,
        },
        yaxis: {

            // categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            labels: {
                formatter: function (value) {
                    return "$" + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
            },
        },
        colors: linechartBasicColors,
        legend: {
            position: 'bottom',
        },
        fill: {
            opacity: 1
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#stacked-column-chart-sales"),
        options
    );

    chart.render();
}
var linechartBasicColorssignup = getChartColorsArray("stacked-column-chart-signup");
if (linechartBasicColorssignup) {
    var options = {
        chart: {
            height: 360,
            type: 'bar',
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },

        plotOptions: {
            bar: {
                labels: {
                    formatter: function (value) {
                        return value.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                },
                borderRadius: 0,
                dataLabels: {
                    position: 'top', // top, center, bottom
                },
            }
        },

        dataLabels: {
            enabled: true
        },

        series: [{
            name: 'Total signup',
            data: chartData.month.monthsignup
            ,
        }],
        xaxis: {
            categories: chartData.month.monthnames,
        },
        yaxis: {

            // categories: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            labels: {
                formatter: function (value) {
                    return value.toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
            },
        },
        colors: linechartBasicColors,
        legend: {
            position: 'bottom',
        },
        fill: {
            opacity: 1
        },
    }

    var chart = new ApexCharts(
        document.querySelector("#stacked-column-chart-signup"),
        options
    );

    chart.render();
}

// Radial chart
var radialbarColors = getChartColorsArray("radialBar-chart");
if (radialbarColors) {
    var options = {
        chart: {
            height: 200,
            type: 'radialBar',
            offsetY: -10
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                dataLabels: {
                    name: {
                        fontSize: '13px',
                        color: undefined,
                        offsetY: 60
                    },
                    value: {
                        offsetY: 22,
                        fontSize: '16px',
                        color: undefined,
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                }
            }
        },
        colors: radialbarColors,
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                shadeIntensity: 0.15,
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 50, 65, 91]
            },
        },
        stroke: {
            dashArray: 4,
        },
        series: [67],
        labels: ['Series A'],

    }

    var chart = new ApexCharts(
        document.querySelector("#radialBar-chart"),
        options
    );

    chart.render();
}
var OverallGender = getChartColorsArray("overall-gender");
console.log("OverallGender", OverallGender);
if (OverallGender) {
    var options23 = {
        series: [chartData.total_user_male, chartData.total_user_female,],
        chart: {
            width: 300,
            type: 'pie',
        },
        labels: ['Male', 'Female'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                dataLabels: {
                    offset: -10
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    }

    var chart2 = new ApexCharts(
        document.querySelector("#overall-gender"),
        options23
    );

    chart2.render();
}
var activegender = getChartColorsArray("active-gender");
if (activegender) {
    var options23 = {
        series: [chartData.active_user_male, chartData.active_user_female],
        chart: {
            width: 300,
            type: 'pie',
        },

        labels: ['Male', 'Female'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                dataLabels: {
                    offset: -10
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    }

    var chart2 = new ApexCharts(
        document.querySelector("#active-gender"),
        options23
    );

    chart2.render();
}

var options23 = {
    series: chartData.saleSkuQty,
    chart: {
        width: 300,
        type: 'pie',
    },

    labels: chartData.saleSkuName,
    legend: {
        show:false
        // position: 'bottom'
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -10
            }
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
}

var chart3 = new ApexCharts(
    document.querySelector("#sale-sku"),
    options23
);

chart3.render();

var options23 = {
    series: chartData.saleLocationQty,
    chart: {
        width: 300,
        type: 'pie',
    },

    labels: chartData.saleLocationName,
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -10
            }
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
}

var chart4 = new ApexCharts(
    document.querySelector("#sale-location"),
    options23
);

chart4.render();

var options23 = {
    series: chartData.saleBrandQty,
    chart: {
        width: 300,
        type: 'pie',
    },

    labels: chartData.saleBrandName,
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -10
            }
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
}

var chart5 = new ApexCharts(
    document.querySelector("#sale-brand"),
    options23
);

chart5.render();

var optionsLine2 = {
    series: [{
        name: "Sign Up",
        data: chartData.rangeWithCount
    }],
    xaxis: {
        categories: chartData.ranges
    },
    chart: {
        id: 'tw',
        group: 'social',
        type: 'area',
        height: 300,
        toolbar: {
            show: false
        },
    },
    colors: ['#3d352b']
};

var chartLine2 = new ApexCharts(document.querySelector("#chart-line2"), optionsLine2);
chartLine2.render();

var optionsSmall = {
    series: [{
        name: "Spending",

        data: chartData.rangeWithSales
    }],
    dataLabels: {
        enabled: true,
        formatter: function (value) {
            return "$" + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },
        // style: {
        //     colors: ['#50cd89']
        // },
    },
    xaxis: {

        categories: chartData.ranges
    },
    yaxis: {
        labels: {
            formatter: function (value) {
                return "$" + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }
        },
        categories: chartData.ranges
    },
    chart: {
        id: 'ig',
        group: 'social',
        type: 'area',
        toolbar: {
            show: false
        },
        height: 300,

    },
    colors: ['#ef3f3b']
};

var chartSmall = new ApexCharts(document.querySelector("#chart-small"), optionsSmall);
chartSmall.render();
