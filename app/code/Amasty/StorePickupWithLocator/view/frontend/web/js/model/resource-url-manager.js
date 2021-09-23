/**
 * Model for getting Store Pickup API query urls
 */
define([
    'mage/url',
    'Magento_Checkout/js/model/url-builder'
], function (mageUrl, urlBuilder) {
    'use strict';

    return {
        /**
         * Getting Store Pickup API url depending on customer login status
         *
         * @param {String} quoteId
         * @param {String} methodName
         * @returns {*}
         */
        getMethodUrl: function (quoteId, methodName) {
            if (window.checkoutConfig.isCustomerLoggedIn) {
                return urlBuilder.createUrl('/amstorepickup/' + methodName, {});
            }

            return urlBuilder.createUrl('/amstorepickup/:cartId/' + methodName, {cartId: quoteId});
        },

        getPathUrl: function (path) {
            return mageUrl.build(path);
        }
    };
});
