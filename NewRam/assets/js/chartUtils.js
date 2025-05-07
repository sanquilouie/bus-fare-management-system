function generateChartOptions(config) {
    const { type = 'line', series, xaxisFormat = 'MMMM', title = '', height = 300 } = config;

    return {
        chart: {
            type,
            height,
            zoom: {
                enabled: true,
                type: 'x',
                autoScaleYaxis: true
            },
            toolbar: {
                show: true
            }
        },
        title: {
            text: title,
            align: 'center'
        },
        series,
        xaxis: {
            type: 'category',
            labels: {
                rotate: -45
            }
        },
        plotOptions: type === 'bar' ? {
            bar: {
                horizontal: false,
                columnWidth: '60%',
                dataLabels: {
                    position: 'top'
                }
            }
        } : {},
        stroke: type === 'line' ? { curve: 'smooth' } : {},
        markers: type === 'line' ? { size: 4 } : {}
    };
}
