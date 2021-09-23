/**
 * grand-total mixin
 */
define([
    'Magento_Checkout/js/model/totals'
],function (totals) {
    'use strict';

    var mixin = {

        /**
         * @return {String}
         */
        getFloatPrice: function (price) {
            return parseFloat(price).toFixed(2);
        },

        /**
         * @return {String|int}
         */
        getGrandTotalExclTax: function () {
            var total = this.totals(),
                grandTotal;

            if (typeof total === 'undefined') {
                return 0;
            }

            grandTotal = total['grand_total'] || 0;

            return this.getFormattedPrice(this.getFloatPrice(grandTotal));
        },

        /**
         * @return {String}
         */
        getValue: function () {
            var price = 0;

            if (typeof this.totals() !== 'undefined') {
                price = totals.getSegment('grand_total').value;
            }

            return this.getFormattedPrice(this.getFloatPrice(price));
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
