var serieData = [47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41, 35];
var criticalChart = {
    chart: {
        id: 'criticalChart',
        group: 'sparklines',
        type: 'area',
        sparkline: {
            enabled: true
        },
    },
    stroke: {
        curve: 'straight',
    },
    fill: {
        opacity: 1,
    },
    series: [{
        name: 'critical services',
        data: serieData,
    }],
}

new ApexCharts(document.querySelector("#critical-chart"), criticalChart).render();
