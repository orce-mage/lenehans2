/**
 * Select Element for dinamic rows
 */
define([
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (_, select) {
    'use strict';

    return select.extend({
        /**
         * Replace value id with label text
         */
        normalizeData: function () {
            var value = this._super(),
                option;

            if (value !== '') {
                option = this.getOption(value);

                return option && option.label;
            }

            if (!this.caption()) {
                return findFirst(this.options);
            }
        }
    });
});