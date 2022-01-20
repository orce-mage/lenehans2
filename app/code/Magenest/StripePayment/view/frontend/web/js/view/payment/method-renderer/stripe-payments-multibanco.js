/**
 * Copyright © 2018 Magenest. All rights reserved.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magenest_StripePayment/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'mage/url',
        'mage/cookies',
        'mage/translate',
    ],
    function ($,
              ko,
              Component,
              setPaymentInformationAction,
              fullScreenLoader,
              additionalValidators,
              messageContainer,
              url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-multibanco',
                redirectAfterPlaceOrder: false
            },
            messageContainer: messageContainer,

            afterPlaceOrder: function () {
                var self = this;
                fullScreenLoader.startLoader();
                self.isPlaceOrderActionAllowed(false);
                $.ajax({
                    url: url.build('stripe/checkout_multibanco/redirect'),
                    dataType: "json",
                    data: {
                        form_key: $.cookie('form_key')
                    },
                    type: 'POST',
                    success: function (response) {
                        if (response.success) {
                            $.mage.redirect(response.redirect_url);
                        }
                        if (response.error) {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    error: function () {
                        self.isPlaceOrderActionAllowed(true);
                        fullScreenLoader.stopLoader();
                        self.messageContainer.addErrorMessage({
                            message: $.mage.__('Something went wrong, please try again.')

                        });
                    }
                });

            },

            getIcons: function () {
                return window.checkoutConfig.payment.magenest_stripe_config.icon.magenest_stripe_multibanco;
            },

            validate: function() {
                return true;
            },
        });
    }
);