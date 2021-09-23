/*jshint browser:true jquery:true*/
/*global alert*/

define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'jquery-ui-modules/slider'
], function ($, priceUtil) {
    "use strict";

    $.widget('magebig.rangeSlider', {

        options: {
            fromLabel: '[data-role=from-label]',
            toLabel: '[data-role=to-label]',
            sliderBar: '[data-role=slider-bar]',
            applyButton: '[data-role=apply-range]',
            rate: 1.0000,
            maxLabelOffset: 0.01
        },

        _create: function () {
            this._initSliderValues();
            this._createSlider();
            this._refreshDisplay();
        },

        _initSliderValues: function () {
            this.rate = parseFloat(this.options.rate);
            this.from = Math.floor(this.options.currentValue.from * this.rate);
            this.to = Math.round(this.options.currentValue.to * this.rate);
            this.minValue = Math.floor(this.options.minValue * this.rate);
            this.maxValue = Math.round(this.options.maxValue * this.rate);
        },

        _createSlider: function () {
            this.element.find(this.options.sliderBar).slider({
                range: true,
                min: this.minValue,
                max: this.maxValue,
                values: [this.from, this.to],
                slide: this._onSliderChange.bind(this),
                step: this.options.step
            });
        },

        _onSliderChange: function (ev, ui) {
            this.from = ui.values[0];
            this.to = ui.values[1];
            this._refreshDisplay();
        },

        _refreshDisplay: function () {
            if (this.element.find('[data-role=from-label]')) {
                this.element.find('[data-role=from-label]').html(this._formatLabel(this.from));
            }

            if (this.element.find('[data-role=to-label]')) {
                this.element.find('[data-role=to-label]').html(this._formatLabel(this.to - this.options.maxLabelOffset));
            }

            this._applyRange();
        },

        _applyRange: function () {
            var from = this.from * (1 / this.rate),
                to = this.to * (1 / this.rate),
                url = this.options.actionUrl,
                code = this.options.code;

            url += (url.search(/\?/) != -1) ? '&' : '?';
            url += code + '=' + from + '-' + to;
            this.element.find(this.options.applyButton).attr('href', url);
        },

        _formatLabel: function (value) {
            var formattedValue = value;

            if (this.options.fieldFormat) {
                formattedValue = priceUtil.formatPrice(value, this.options.fieldFormat);
            }

            return formattedValue;
        }
    });

    return $.magebig.rangeSlider;
});
