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
        'Magento_Checkout/js/model/quote',
        'mage/translate',
    ],
    function (
        $,
        ko,
        Component,
        setPaymentInformationAction,
        fullScreenLoader,
        additionalValidators,
        messageContainer,
        url,
        quote
    ) {
        'use strict';

        var stripe, elements, idealBank, errorMessage;
        var totals = quote.totals(),
            zeroDecimal = window.checkoutConfig.payment.magenest_stripe_config.isZeroDecimal,
            currency = totals.base_currency_code;

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-ideal',
                redirectAfterPlaceOrder: false
            },
            messageContainer: messageContainer,
            bankList: ko.observableArray(JSON.parse(window.checkoutConfig.payment.magenest_stripe_ideal.bank_list)),
            bankValue: ko.observable(''),
            allowSelectBank: ko.observable(''),
            useElementInterface: ko.observable(false),
            publicKey: window.checkoutConfig.payment.magenest_stripe_config.publishableKey,

            initObservable: function(){
                var self = this;
                this._super();
                this.bankValue(window.checkoutConfig.payment.magenest_stripe_ideal.default_bank);
                this.allowSelectBank(window.checkoutConfig.payment.magenest_stripe_ideal.is_allow_select_bank);
                this.useElementInterface(window.checkoutConfig.payment.magenest_stripe_ideal.is_use_element_interface);
                return this;
            },

            initStripeIdeal: function(){
                if (this.validate()) {
                    var self = this;
                    stripe = Stripe(self.publicKey);
                    elements = stripe.elements();
                    var style = {
                        base: {
                            padding: '10px 12px',
                            color: '#32325d',
                            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                            fontSmoothing: 'antialiased',
                            fontSize: '16px',
                            '::placeholder': {
                                color: '#aab7c4'
                            },
                        },
                        invalid: {
                            color: '#fa755a',
                        }
                    };

                    idealBank = elements.create('idealBank', {style: style});
                    idealBank.mount('#ideal-bank-element');

                    errorMessage = $('#' + self.getCode() + '-error-message');

                    var form = $('#' + self.getCode() + '-payment-form');
                }
            },

            validate: function () {
                var self = this;
                return this._super();
            },

            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }
                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    fullScreenLoader.startLoader();
                    $.when(
                        setPaymentInformationAction(this.messageContainer, self.getData())
                    ).done(
                        function () {
                            self.isPlaceOrderActionAllowed(false);
                            fullScreenLoader.startLoader();
                            if(self.useElementInterface()){
                                var amount = totals.base_grand_total;
                                if(!zeroDecimal){
                                    amount*=100;
                                }
                                var sourceData = {
                                    type: 'ideal',
                                    amount: Math.round(amount),
                                    currency: currency.toLowerCase(),
                                    owner: {
                                        name: $('#ideal-name').val(),
                                    },
                                    redirect: {
                                        return_url: url.build("stripe/checkout_ideal/response"),
                                    },
                                };

                                stripe.createSource(idealBank, sourceData).then(function (result) {
                                    if (result.error) {
                                        errorMessage.html(result.error.message);
                                        errorMessage.addClass("visible");
                                        fullScreenLoader.stopLoader();
                                        self.isPlaceOrderActionAllowed(true);
                                    } else {
                                        var redirectUrl = result.source.redirect.url;
                                        errorMessage.removeClass("visible");
                                        $.ajax({
                                            url: url.build('stripe/checkout_ideal/update'),
                                            dataType: "json",
                                            data: {
                                                form_key: $.cookie('form_key'),
                                                source: result.source
                                            },
                                            type: 'POST',
                                            success: function (response) {
                                                if (response.success) {
                                                    $.mage.redirect(redirectUrl);
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
                                    }
                                });
                            } else {
                                $.ajax({
                                    url: url.build('stripe/checkout_ideal/source'),
                                    dataType: "json",
                                    data: {
                                        form_key: $.cookie('form_key'),
                                        bankValue: self.bankValue()
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
                            }
                        }
                    ).always(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                        }
                    );

                    return true;
                }

                return false;
            },

            getCode: function () {
                return 'magenest_stripe_ideal';
            },

            getIcons: function () {
                return window.checkoutConfig.payment.magenest_stripe_config.icon.magenest_stripe_ideal;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.magenest_stripe_ideal.instructions;
            },
        });
    }
);