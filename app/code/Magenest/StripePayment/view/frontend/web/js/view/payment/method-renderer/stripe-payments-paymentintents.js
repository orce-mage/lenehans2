define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'mage/cookies',
        'mage/translate',
        'Magento_Checkout/js/action/set-payment-information',
    ],
    function (Component, $, ko, quote, customer, fullScreenLoader, redirectOnSuccessAction, messageContainer, additionalValidators, url, setPaymentInformationAction) {
        'use strict';

        var stripe, elements, cardElement, errorMessage;
        var totals = quote.totals();
        var dataSecret =  '';

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-paymentintents',
                redirectAfterPlaceOrder: true,
                source_id: "",
            },

            getCode: function() {
                return 'magenest_stripe_paymentintents';
            },

            isActive: function() {
                return true;
            },

            getClientSecret: function () {
                var self = this;
                return $.get(
                    url.build("stripe/checkout_paymentintents/data"),
                    {
                        form_key: $.cookie('form_key')
                    },
                    function (response) {
                        if (response.success) {
                            dataSecret = response.clientSecret;
                        }
                        if (response.error){
                            fullScreenLoader.stopLoader();
                            console.log(response);
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    "json"
                );
            },


            initStripePaymentIntents: function(){
                if (this.validate()) {
                    var self = this;
                    stripe = Stripe(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
                    var style = {
                        base: {
                            iconColor: '#666ee8',
                            color: '#31325f',
                            fontWeight: 400,
                            fontFamily:
                                '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
                            fontSmoothing: 'antialiased',
                            fontSize: '15px',
                            '::placeholder': {
                                color: '#aab7c4',
                            },
                            ':-webkit-autofill': {
                                color: '#666ee8',
                            },
                        },
                    };
                    elements = stripe.elements();
                    cardElement = elements.create('card', {
                        style: style,
                        hidePostalCode: true,
                    });
                    cardElement.mount('#' + self.getCode() + '-card-element');

                    cardElement.addEventListener('change', function (event) {
                        var displayError = document.getElementById(self.getCode() + '-card-errors');
                        if (event.error) {
                            displayError.textContent = event.error.message;
                        } else {
                            displayError.textContent = '';
                        }
                    });
                }
            },

            placeOrder: function (data, event) {

                if (event) {
                    event.preventDefault();
                }
                var self = this;
                var address = quote.billingAddress();
                var firstName = quote.billingAddress().firstname;
                var lastName = quote.billingAddress().lastname;
                var ownerInfo = {
                    payment_method_data: {
                        billing_details: {
                            name: firstName + ' ' + lastName,
                            address: {
                                line1: address.street[0],
                                line2: address.street[1],
                                city: address.city,
                                postal_code: address.postcode,
                                country: address.countryId,
                                state: address.region
                            },
                            email: (!customer.customerData.email) ? quote.guestEmail : customer.customerData.email
                        }
                    }
                };
                if (address.telephone) {
                    ownerInfo.payment_method_data.billing_details.phone = address.telephone;
                }
                if (this.validate() && additionalValidators.validate()) {
                    fullScreenLoader.startLoader();
                    this.getClientSecret().done(function () {
                        self.isPlaceOrderActionAllowed(false);
                        stripe.handleCardPayment(dataSecret, cardElement, ownerInfo).then(function (result) {
                            if (result.error) {
                                fullScreenLoader.stopLoader();
                                self.isPlaceOrderActionAllowed(true);
                                var errorElement = document.getElementById(self.getCode() + '-card-errors');
                                errorElement.textContent = result.error.message;
                            } else {
                                self.source_id = result.paymentIntent.id;
                                self.realPlaceOrder();
                            }
                        });
                    });
                }

            },

            realPlaceOrder: function () {
                var self = this;
                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            fullScreenLoader.stopLoader(true);
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
                        "source_id": self.source_id
                    }
                }
            },


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
                if(window.checkoutConfig.payment.magenest_stripe_paymentintents === "" ){
                    self.messageContainer.addErrorMessage({
                        message: $.mage.__("No Data Secret provided.")
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