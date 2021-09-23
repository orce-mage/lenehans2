define([
    'jquery',
    'chartJs',
    'moment'
], function ($, Chart, moment) {
    $.widget('mst.warmerChart', {
        options: {
            id: '',
            type: '',
            data: null,
            chartOptions: null,
        },

        id: null,
        type: null,
        data: null,
        chartOptions: null,

        _create: function () {
            this.id = this.options.id;
            this.type = this.options.type;
            this.data = this.options.data;
            this.chartOptions = this.options.chartOptions;

            if (this.type === 'line') {
                var yAxesConfig = this.chartOptions.scales.yAxes[0];

                yAxesConfig.ticks.callback = function (value, index, values) {
                    return value + '%';
                }

                this.chartOptions.tooltips.callbacks = {
                    label: function (tooltipItem, data) {
                        return tooltipItem.yLabel + '%';
                    },
                    title: function (tooltipItems, data) {
                        return moment(tooltipItems[0].xLabel).format('MMMM Do YYYY, H:mm');
                    }
                }

                this.chartOptions.scales.yAxes = [yAxesConfig];
            } else {
                this.chartOptions.tooltips.callbacks = {
                    label: function (tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + '%';
                    }
                }

                this.chartOptions.tooltips.mode = 'point';
            }

            var chartConfig = {
                type: this.type,
                data: this.data,
                options: this.chartOptions
            }

            var chart = new Chart(
                $("#" + this.id),
                chartConfig
            );
        }
    });

    return $.mst.warmerChart;
});
