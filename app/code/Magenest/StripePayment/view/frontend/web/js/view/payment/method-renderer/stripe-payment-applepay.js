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
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'mage/translate',
    ],
    function ($,
              ko,
              Component,
              placeOrderAction,
              setPaymentInformationAction,
              fullScreenLoadern,
              checkoutData,
              quote,
              fullScreenLoader,
              redirectOnSuccessAction,
              additionalValidators,
              messageContainer,
              $t
    ) {
        'use strict';

        var stripe,
            paymentRequest;
        var totals = quote.totals(),
            zeroDecimal = window.checkoutConfig.payment.magenest_stripe_config.isZeroDecimal,
            currency = totals.base_currency_code;

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-applepay',
                replacePlaceOrder: Boolean(window.checkoutConfig.payment.magenest_stripe_applepay.replace_placeorder === "1"),
                rawCardData:"",
                activeOnCheckout: window.checkoutConfig.payment.magenest_stripe_applepay.active_on_checkout,
                isPaymentButtonLoaded: false,
                shouldDisplayPlaceOrderButton: ko.observable(true)
            },
            messageContainer: messageContainer,

            initObservable: function (){
                var self = this;
                this._super();
                this.shouldDisplayPlaceOrderButton(!this.replacePlaceOrder);
                return this;
            },

            placeOrder: function () {
                var self = this;
                if(this.validate() && additionalValidators.validate()){
                    this.isPlaceOrderActionAllowed(false);
                    this.shouldDisplayPlaceOrderButton(false);
                    if(!this.isPaymentButtonLoaded){
                        this.requestButton(true);
                    }
                }
            },

            requestButton: function (isDisplay){
                if(this.validate()) {
                    this.isPaymentButtonLoaded = true;
                    stripe = Stripe(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
                    var self = this;
                    var amount = totals.base_grand_total;
                    if (!zeroDecimal) {
                        amount *= 100;
                    }
                    paymentRequest = stripe.paymentRequest({
                        country: window.checkoutConfig.payment.magenest_stripe_config.country_code,
                        currency: currency.toLowerCase(),
                        total: {
                            label: $t('Shopping Cart'),
                            amount: Math.round(amount),
                            pending: true
                        },
                        displayItems: self.getDisplayItems(),
                        requestPayerName: true,
                        requestPayerEmail: true
                    });

                    var elements = stripe.elements();
                    if (self.replacePlaceOrder || isDisplay) {
                        var prButton = elements.create('paymentRequestButton', {
                            paymentRequest: paymentRequest,
                            style: {
                                paymentRequestButton: {
                                    type: window.checkoutConfig.payment.magenest_stripe_applepay.button_type,
                                    theme: window.checkoutConfig.payment.magenest_stripe_applepay.button_theme,
                                    height: '40px'
                                }
                            }
                        });
                        // Check the availability of the Payment Request API first.
                        paymentRequest.canMakePayment().then(function (result) {
                            console.log(result);
                            if (result) {
                                prButton.mount('#payment_section');
                            } else {
                                self.messageContainer.addErrorMessage({
                                    message: $.mage.__("Error: Current payment method is unavailable")
                                });
                                document.getElementById('payment_section').style.display = 'none';
                            }
                        });
                    }

                    paymentRequest.on('token', function (ev) {
                        // Send the token to your server to charge it!
                        self.rawCardData = ev.token;
                        self.getPlaceOrderDeferredObject()
                            .fail(function () {
                                ev.complete('fail');
                            })
                            .done(function () {
                                    ev.complete('success');
                                    self.afterPlaceOrder();
                                    if (self.redirectAfterPlaceOrder) {
                                        redirectOnSuccessAction.execute();
                                    }
                                }
                            );
                    });

                    quote.getTotals().subscribe(function (value) {
                        var amount = value.base_grand_total;
                        if (!zeroDecimal) {
                            amount *= 100;
                        }
                        paymentRequest.update({
                            currency: currency.toLowerCase(),
                            total: {
                                label: $t('Shopping Cart'),
                                amount: Math.round(amount),
                                pending: true
                            },
                            displayItems: self.getDisplayItems(),
                        });
                    });
                }else{
                    this.shouldDisplayPlaceOrderButton(true);
                }
            },

            requestPayment: function (data, event, parent) {
                if(parent.replacePlaceOrder){
                    return parent.requestButton(false);
                }
            },

            /**
             * @return {*}
             */
            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
            },

            /**
             * Get payment method data
             */
            getData: function () {
                var self = this;
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "stripe_response": JSON.stringify(self.rawCardData)
                    }
                };
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.magenest_stripe_applepay.instructions;
            },

            getDisplayItems: function () {
                var arr = [];
                var items = quote.getItems();
                var amount;
                items.forEach(function (v ,i) {
                    amount = v.row_total;
                    if(!zeroDecimal){
                        amount*=100;
                    }
                    arr.push({
                        amount: Math.round(amount),
                        label: v.name,
                        pending: true
                    })
                });
                return arr;
            },

        });

    }
);
