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
        'mage/translate',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Customer/js/model/customer',
    ],
    function ($,
              ko,
              Component,
              setPaymentInformationAction,
              fullScreenLoader,
              additionalValidators,
              messageContainer,
              url,
              $t,
              quote,
              redirectOnSuccessAction,
              customer
    ) {
        'use strict';

        var stripe;

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-wechatpay',
                redirectAfterPlaceOrder: true,
                source: ""
            },
            messageContainer: messageContainer,

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self  = this;
                var amount = quote.totals().base_grand_total;
                var currency = quote.totals().base_currency_code;
                var address = quote.billingAddress();
                var firstName = quote.billingAddress().firstname;
                var lastName = quote.billingAddress().lastname;
                if (this.validate() && additionalValidators.validate()) {
                    stripe = Stripe(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
                    self.isPlaceOrderActionAllowed(false);
                    stripe.createSource({
                        type: 'wechat',
                        amount: amount*100,
                        currency: currency,
                        owner: {
                            name: firstName +" "+ lastName,
                            address: {
                                line1: address.street[0],
                                line2: address.street[1],
                                city: address.city,
                                postal_code: address.postcode,
                                country: address.countryId,
                                state: address.region
                            },
                            email: (!customer.customerData.email)?quote.guestEmail:customer.customerData.email
                        },
                    }).then(function (result) {
                        if (result.error) {
                            self.isPlaceOrderActionAllowed(true);
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        } else {
                            self.source = result.source;
                            window.open(result.source.wechat.qr_code_url);
                            self.realPlaceOrder();
                        }
                    });
                }
            },

            realPlaceOrder: function () {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                    function () {
                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );
            },

            getData: function() {
                var self = this;
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "stripe_response": JSON.stringify(self.source)
                    }
                }
            },

            getIcons: function () {
                return window.checkoutConfig.payment.magenest_stripe_config.icon.magenest_stripe_wechatpay;
            }
        });
    }
);