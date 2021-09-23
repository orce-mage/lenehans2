/**
 * Datepicker logic
 */

define([
    'uiComponent',
    'jquery',
    'mage/translate',
    'mage/calendar'
], function (Component, $, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            timeValue: '',
            isSendLater: 0,
            timezones: [],
            datepickerSelector: '[data-amcard-js="datepicker"]',
            datepickerId: '#ui-datepicker-div',
            amDatepickerClass: 'am-datepicker',
            imports: {
                preconfiguredValues: '${ "giftCard" }:preconfiguredValues'
            }
        },

        initObservable: function () {
            this._super().observe(['timeValue', 'isSendLater', 'timezones']);

            return this;
        },

        initDatepicker: function () {
            $(this.datepickerSelector).calendar({
                minDate: new Date(),
                showButtonPanel: true,
                currentText: $t('Go Today'),
                changeMonth: true,
                changeYear: true,
            });
            $(this.datepickerId).addClass(this.amDatepickerClass);
        },

        validateValue: function () {
            var currentDate = Date.now();

            if (+new Date(this.timeValue) < currentDate) {
                $(this.datepickerSelector).datepicker('setDate', currentDate);
            }
        },

        getSheduleDeliveryType: function (name) {
            if (this.preconfiguredValues[name]) {
                this.isSendLater(+this.preconfiguredValues[name]);
            }
        },

        getPreconfiguredValue: function (name) {
            if (this.preconfiguredValues[name]) {
                return this.preconfiguredValues[name];
            }
        }
    });
});
