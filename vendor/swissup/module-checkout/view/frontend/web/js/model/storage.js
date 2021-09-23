define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    function getCheckoutData(namespace) {
        var checkoutData = customerData.get('checkout-data')();

        if (!checkoutData.swissup) {
            checkoutData.swissup = {};
        }

        if (!checkoutData.swissup[namespace]) {
            checkoutData.swissup[namespace] = {};
        }

        return checkoutData;
    }

    function initData(namespace) {
        var data = customerData.get('checkout-data')();

        if (!data.swissup || !data.swissup[namespace]) {
            return {};
        }

        return data.swissup[namespace];
    }

    /**
     * var vault = storage('namespace');
     * vault.set('key', value);
     * vault.get('key');
     *
     * @param {String} namespace
     * @return {Object}
     */
    return function (namespace) {
        var data = initData(namespace);

        return {
            /**
             * @param {String} key
             * @param {mixed} defaults
             * @return {mixed}
             */
            get: function (key, defaults) {
                return data[key] === undefined ? defaults : data[key];
            },

            /**
             * @param {String|Object} key
             * @param {mixed} value
             */
            set: function (key, value) {
                var checkoutData = getCheckoutData(namespace);

                if (typeof key === 'object') {
                    data = key;
                } else {
                    data[key] = value;
                }

                checkoutData.swissup[namespace] = data;

                customerData.set('checkout-data', checkoutData);
            }
        }
    }
});
