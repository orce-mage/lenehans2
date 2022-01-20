/**
 * Copyright © 2018 Magenest. All rights reserved.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-direct'
            },

            getCode: function() {
                return 'magenest_stripe';
            },

            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            },

            validate: function () {
                if(window.checkoutConfig.payment.magenest_stripe_config.https_check){
                    if (window.location.protocol !== "https:") {
                        self.messageContainer.addErrorMessage({
                            message: $.mage.__("Error: HTTPS is not enabled")
                        });
                        return false;
                    }
                }
                return this.validateForm($('#'+this.getCode()+'-form'));
            },

            isActive: function() {
                return true;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.magenest_stripe.instructions;
            }
        });
    }
);
