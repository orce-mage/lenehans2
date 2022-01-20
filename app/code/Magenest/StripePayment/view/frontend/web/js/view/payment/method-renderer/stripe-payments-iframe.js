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
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
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
              checkoutData,
              quote,
              customer,
              fullScreenLoader,
              redirectOnSuccessAction,
              additionalValidators,
              messageContainer,
              url
    ) {
        'use strict';

        var handler;

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-iframe',
                redirectAfterPlaceOrder: false,
                api_response: ""
            },
            messageContainer: messageContainer,

            initialize: function () {
                this._super();
                this.loadStripeCheckout();
                Stripe.setPublishableKey(window.checkoutConfig.payment.magenest_stripe_config.publishableKey);
            },
            loadStripeCheckout: function (callback) {
                if (typeof StripeCheckout === "undefined")
                {
                    var script = document.createElement('script');
                    script.onload = function() {
                        handler = StripeCheckout.configure({
                            key: window.checkoutConfig.payment.magenest_stripe_config.publishableKey
                        });
                    };
                    script.onerror = function(response) {
                        console.log("stripe checkout load error");
                        console.log(response);
                    };
                    script.src = "https://checkout.stripe.com/checkout.js";
                    document.head.appendChild(script);
                }
                else {
                    handler = StripeCheckout.configure({
                        key: window.checkoutConfig.payment.magenest_stripe_config.publishableKey
                    })
                }
            },

            bodyFreezeScroll: function () {
                var bodyWidth = window.document.body.offsetWidth;
                var css = window.document.body.style;
                css.overflow = "hidden";
                css.marginTop = "0px";
                css.marginRight = (css.marginRight ? '+=' : '') + (window.document.body.offsetWidth - bodyWidth);
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                var self = this;
                if (this.validate() && additionalValidators.validate()) {
                    self.isPlaceOrderActionAllowed(false);
                    var totals = quote.totals(),
                        canCollectBilling = Boolean(window.checkoutConfig.payment.magenest_stripe_iframe.can_collect_billing === '1'),
                        canCollectShipping = Boolean(window.checkoutConfig.payment.magenest_stripe_iframe.can_collect_shipping === '1'),
                        canCollectZipCode = Boolean(window.checkoutConfig.payment.magenest_stripe_iframe.can_collect_zip === '1'),
                        allowRemember = Boolean(window.checkoutConfig.payment.magenest_stripe_iframe.allow_remember === '1'),
                        acceptBitcoin = Boolean(window.checkoutConfig.payment.magenest_stripe_iframe.accept_bitcoin === '1'),
                        acceptAlipay = Boolean(window.checkoutConfig.payment.magenest_stripe_iframe.accept_alipay === '1'),
                        displayName = window.checkoutConfig.payment.magenest_stripe_iframe.display_name,
                        image = window.checkoutConfig.payment.magenest_stripe_iframe.image_url,
                        locale = window.checkoutConfig.payment.magenest_stripe_iframe.locale,
                        panelLabel = window.checkoutConfig.payment.magenest_stripe_iframe.button_label,
                        zeroDecimal = window.checkoutConfig.payment.magenest_stripe_config.isZeroDecimal;
                    var amount = totals.base_grand_total;
                    if(!zeroDecimal){
                        amount*=100;
                    }
                    handler.open({
                        name: displayName,
                        amount: amount,
                        currency: totals.base_currency_code,
                        email: (!customer.customerData.email) ? quote.guestEmail : customer.customerData.email,
                        billingAddress: false,
                        locale: locale,
                        zipCode: canCollectZipCode,
                        image: image,
                        allowRememberMe: allowRemember,
                        bitcoin: false,
                        alipay: false,
                        panelLabel: panelLabel,
                        token: function (response, args) {
                            self.payType = response.type;
                            if (response.type === 'card') {
                                var address = quote.billingAddress();
                                var owner = {
                                    address: {
                                        postal_code: response.card.address_zip,
                                        city: response.card.address_city,
                                        country: response.card.country,
                                        line1: response.card.address_line1,
                                        line2: response.card.address_line2,
                                        state: response.card.address_state
                                    },
                                    name: response.card.name,
                                    email: response.email
                                };
                                if(!canCollectZipCode){
                                    owner.address.postal_code = address.postcode;
                                }
                                if(!canCollectBilling){
                                    owner.address.city = address.city;
                                    owner.address.country = address.countryId;
                                    owner.address.line1 = address.street[0];
                                    owner.address.line2 = address.street[1];
                                    owner.address.state = address.region;
                                }

                                Stripe.source.create({
                                    type: 'card',
                                    token: response.id,
                                    owner: owner
                                }, function (status, response) {
                                    if (response.error) {
                                        self.messageContainer.addErrorMessage({
                                            message: response.error.message
                                        });
                                        self.isPlaceOrderActionAllowed(true);
                                    } else {
                                        self.api_response = response;
                                        self.realPlaceOrder();
                                    }
                                });
                            }else{
                                self.isPlaceOrderActionAllowed(true);
                                self.messageContainer.addErrorMessage({
                                    message: $.mage.__("Operation not allowed")
                                });
                                // self.api_response = response;
                                // self.realPlaceOrder();
                            }

                        },
                        opened: function () {
                            self.bodyFreezeScroll();
                        },
                        closed: function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    });

                    window.addEventListener('popstate', function() {
                        handler.close();
                    });
                }
                return false;
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
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );
            },

            afterPlaceOrder: function () {
                var self = this;
                $.post(
                    // url.build("stripe/checkout/threedSecure"),
                    url.build("stripe/checkout_secure/redirect"),
                    {
                        form_key: $.cookie('form_key')
                    },
                    function (response) {
                        if (response.success) {
                            if(response.defaultPay){
                                redirectOnSuccessAction.execute();
                            }
                            if(response.threeDSercueActive){
                                window.location = response.threeDSercueUrl;
                            }

                        }
                        if (response.error){
                            self.isPlaceOrderActionAllowed(true);
                            console.log(response);
                            self.messageContainer.addErrorMessage({
                                message: response.message
                            });
                        }
                    },
                    "json"
                );
            },

            isActive: function() {
                return true;
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
                return window.checkoutConfig.payment.magenest_stripe_iframe.instructions;
            }
        });

    }
);
