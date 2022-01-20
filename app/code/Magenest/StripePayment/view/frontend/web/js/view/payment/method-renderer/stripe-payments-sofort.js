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
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-billing-address',
        'mage/url',
        'mage/translate',
    ],
    function ($,
              ko,
              Component,
              setPaymentInformationAction,
              checkoutData,
              quote,
              fullScreenLoader,
              redirectOnSuccessAction,
              additionalValidators,
              messageContainer,
              customer,
              setBillingAddressAction,
              url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magenest_StripePayment/payment/stripe-payments-sofort',
                redirectAfterPlaceOrder: false
            },
            messageContainer: messageContainer,
            country: ko.observable(''),
            language: ko.observable(''),
            allowSelectLanguage: ko.observable(''),
            allowSelectBankCountry: ko.observable(''),
            languageList: ko.observableArray(JSON.parse(window.checkoutConfig.payment.magenest_stripe_sofort.language_list)),
            bankList: ko.observableArray(JSON.parse(window.checkoutConfig.payment.magenest_stripe_sofort.bank_list)),

            initObservable: function(){
                var self = this;
                this._super();
                this.language(window.checkoutConfig.payment.magenest_stripe_sofort.default_language);
                this.country(window.checkoutConfig.payment.magenest_stripe_sofort.default_bank_country);
                this.allowSelectLanguage(window.checkoutConfig.payment.magenest_stripe_sofort.allow_select_language);
                this.allowSelectBankCountry(window.checkoutConfig.payment.magenest_stripe_sofort.allow_select_bank_country);
                return this;
            },

            validate: function () {
                return $('#'+this.getCode() + '-form').valid();
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
                            $.ajax({
                                url: url.build('stripe/checkout_sofort/source'),
                                dataType: "json",
                                data: {
                                    form_key: $.cookie('form_key'),
                                    country: self.country(),
                                    language: self.language()
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
                return 'magenest_stripe_sofort';
            },

            getIcons: function () {
                return window.checkoutConfig.payment.magenest_stripe_config.icon.magenest_stripe_sofort;
            },

            getInstructions: function () {
                return window.checkoutConfig.payment.magenest_stripe_sofort.instructions;
            },
        });
    }
);