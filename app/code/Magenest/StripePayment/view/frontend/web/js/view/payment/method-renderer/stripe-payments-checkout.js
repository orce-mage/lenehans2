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
                template: 'Magento_Payment/payment/free',

            },
            redirectAfterPlaceOrder: false,
            messageContainer: messageContainer,

            afterPlaceOrder: function(){
                var self = this;
                $.post(
                    url.build("stripe/checkout_checkout/redirect"),
                    {
                        form_key: $.cookie('form_key')
                    },
                    function (response) {
                        if (response.success) {
                            if(response.session_id){
                                var stripe = Stripe(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
                                stripe.redirectToCheckout({
                                    sessionId: response.session_id
                                }).then(function (result) {
                                    if(result.error.message){
                                        self.isPlaceOrderActionAllowed(true);
                                        self.messageContainer.addErrorMessage({
                                            message: result.error.message
                                        });
                                    }
                                });
                            }else{
                                self.messageContainer.addErrorMessage({
                                    message: "Session create error"
                                });
                            }
                        }
                        if (response.error){
                            self.isPlaceOrderActionAllowed(true);
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    "json"
                );
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': {}
                };
            },

            isAvailable: function () {
                return true;
            },

            validate: function() {
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

    }
);
