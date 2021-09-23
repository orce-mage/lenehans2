define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        isIconOnly: false,
        MODEL_PREFIX: 'status_',

        /**
         *
         * @param {object} totalsItem
         * @return {array|null}
         */
        getStockstatusInfo: function (totalsItem) {
            if (totalsItem['extension_attributes']
                && totalsItem['extension_attributes']['stockstatus_information'] !== undefined
            ) {
                return totalsItem['extension_attributes']['stockstatus_information'];
            }

            return null;
        },

        /**
         *
         * @param {object} totalsItem
         * @param {string} key
         * @return {string|null}
         */
        getStatusData: function (totalsItem, key) {
            var stockstatusInfo = this.getStockstatusInfo(totalsItem);

            return stockstatusInfo[key] || stockstatusInfo[this.MODEL_PREFIX + key] ||null;
        },

        /**
         *
         * @param totalsItem
         * @return {string|null}
         */
        getStatusIcon: function(totalsItem) {
            return this.getStatusData(totalsItem, 'icon');
        },

        /**
         *
         * @param {object} totalsItem
         * @return {string|null}
         */
        getStatusMessage: function (totalsItem) {
            return this.getStatusData(totalsItem, 'message');
        },

        /**
         *
         * @param {object} totalsItem
         * @return {string|null}
         */
        getStatusId: function (totalsItem) {
            return this.getStatusData(totalsItem, 'id');
        },

        /**
         *
         * @param {object} totalsItem
         * @return {string|null}
         */
        getTooltipText: function (totalsItem) {
            return this.getStatusData(totalsItem, 'tooltip_text');
        },

        /**
         *
         * @param {object} totalsItem
         * @return {boolean}
         */
        canDisplay: function (totalsItem) {
            return this.getStockstatusInfo(totalsItem) !== null;
        }
    });
});
