/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Checkout/js/view/payment/default',
], function (
    ko,
    $,
    Component,
) {
    'use strict';

    return Component.extend({
        /**
         * @return {Boolean}
         */
        validate: function() {
            var self = this;
            if(window.checkoutConfig.payment.magenest_stripe_config.https_check){
                if (window.location.protocol !== "https:") {
                    self.messageContainer.addErrorMessage({
                        message: $.mage.__("Error: HTTPS is not enabled")
                    });
                    return false;
                }
            }
            if(window.checkoutConfig.payment.magenest_stripe_config.publishableKey === "" ){
                self.messageContainer.addErrorMessage({
                    message: $.mage.__("No API key provided.")
                });
                return false;
            }
            if (typeof Stripe === "undefined"){
                self.messageContainer.addErrorMessage({
                    message: $.mage.__("Stripe js load error")
                });
                return false;
            }

            return true;
        },
    });
});
