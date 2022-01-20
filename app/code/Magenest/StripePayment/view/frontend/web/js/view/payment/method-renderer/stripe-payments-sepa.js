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
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/redirect-on-success',
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
        quote,
        customer,
        redirectOnSuccessAction
    ) {
        'use strict';

        var stripe, elements, errorMessage, iban, bankName;
        var totals = quote.totals();

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-sepa',
                redirectAfterPlaceOrder: true,
                api_response:""
            },
            messageContainer: messageContainer,
            publicKey: window.checkoutConfig.payment.magenest_stripe_config.publishableKey,

            initStripeElement: function(){
                var self = this;
                if (this.validate()) {
                    stripe = Stripe(self.publicKey);
                    elements = stripe.elements();
                    var style = {
                        base: {
                            color: '#32325d',
                            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                            fontSmoothing: 'antialiased',
                            fontSize: '16px',
                            '::placeholder': {
                                color: '#aab7c4'
                            },
                            ':-webkit-autofill': {
                                color: '#32325d',
                            },
                        },
                        invalid: {
                            color: '#fa755a',
                            iconColor: '#fa755a',
                            ':-webkit-autofill': {
                                color: '#fa755a',
                            },
                        }
                    };

                    iban = elements.create('iban', {
                        style: style,
                        supportedCountries: ['SEPA'],
                    });
                    iban.mount('#iban-element');
                    errorMessage = $('#' + self.getCode() + '-error-message');
                    bankName = $('#' + self.getCode() + '-bank-name');

                    iban.on('change', function(event) {
                        // Handle real-time validation errors from the iban Element.
                        if (event.error) {
                            errorMessage.html(event.error.message);
                            errorMessage.addClass('visible');
                        } else {
                            errorMessage.removeClass('visible');
                        }

                        // Display bank name corresponding to IBAN, if available.
                        if (event.bankName) {
                            bankName.html(event.bankName);
                            bankName.addClass('visible');
                        } else {
                            bankName.removeClass('visible');
                        }
                    });
                }
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
                            var firstName = quote.billingAddress().firstname;
                            var lastName = quote.billingAddress().lastname;
                            var email = (!customer.customerData.email) ? quote.guestEmail : customer.customerData.email;
                            fullScreenLoader.startLoader();
                            var sourceData = {
                                type: 'sepa_debit',
                                currency: 'eur',
                                owner: {
                                    name: firstName + " " + lastName,
                                    email: email,
                                },
                                mandate: {
                                    // Automatically send a mandate notification email to your customer
                                    // once the source is charged.
                                    notification_method: 'email',
                                }
                            };
                            stripe.createSource(iban, sourceData).then(function (result) {
                                if (result.error) {
                                    errorMessage.html(result.error.message);
                                    errorMessage.addClass('visible');
                                    fullScreenLoader.stopLoader();
                                    self.isPlaceOrderActionAllowed(true);
                                } else {
                                    errorMessage.removeClass('visible');
                                    $.ajax({
                                        url: url.build('stripe/checkout_sepa/update'),
                                        dataType: "json",
                                        data: {
                                            form_key: $.cookie('form_key'),
                                            source: result.source
                                        },
                                        type: 'POST',
                                        success: function (response) {
                                            if (response.success) {
                                                self.api_response = result.source;
                                                self.realPlaceOrder();
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

            realPlaceOrder: function () {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                    function () {
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );
            },

            getCode: function () {
                return 'magenest_stripe_sepa';
            },

            getIcons: function () {
                return window.checkoutConfig.payment.magenest_stripe_config.icon.magenest_stripe_sepa;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        "stripe_response": JSON.stringify(this.api_response)
                    }
                }
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.magenest_stripe_sepa.instructions;
            },
        });
    }
);