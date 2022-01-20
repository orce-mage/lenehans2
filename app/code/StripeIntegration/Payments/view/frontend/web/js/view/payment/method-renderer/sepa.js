/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'StripeIntegration_Payments/js/view/payment/method-renderer/method'
    ],
    function (
        ko,
        $,
        Component
    ) {
        'use strict';

        var iban = window.checkoutConfig.payment.stripe_payments_sepa.iban;
        var company = window.checkoutConfig.payment.stripe_payments_sepa.company;

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/sepa',
                code: "sepa"
            },
            redirectAfterPlaceOrder: false,

            initObservable: function()
            {
                this._super();

                this.hasIcons = ko.pureComputed(function()
                {
                    return (window.checkoutConfig.payment["stripe_payments"].icons.length > 0);
                }, this);

                this.iconsRight = ko.pureComputed(function()
                {
                    if (window.checkoutConfig.payment["stripe_payments"].iconsLocation == "right")
                        return true;
                    return false;
                }, this);

                return this;
            },

            getIcon: function()
            {
                if (typeof this.code == "undefined")
                    return "";

                return window.checkoutConfig.payment["stripe_payments"].apmIcons[this.code];
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'iban': $('#' + this.getCode() + '_iban').val()
                    }
                };
            },

            /**
             * Get Iban
             * @returns string
             */
            getIban: function () {
                return iban;
            },

            /**
             * Get Company
             * @returns string
             */
            getCompany: function () {
                return company;
            }
        });
    }
);
