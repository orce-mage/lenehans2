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

        var collectBankAccount = window.checkoutConfig.payment.stripe_payments_sepa_credit.customer_bank_account;

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/sepa_credit',
                code: "sepa_credit"
            },
            sender_iban: ko.observable(null),
            sender_name: ko.observable(null),
            requiredClass: ko.observable(null),

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

                this.showCustomerBankAccountFields = ko.pureComputed(function()
                {
                    return (this.isOptional() || this.isRequired());
                }, this);

                if (this.isRequired())
                    this.requiredClass("field required");
                else
                    this.requiredClass("field");

                return this;
            },

            getIcon: function()
            {
                if (typeof this.code == "undefined")
                    return "";

                return window.checkoutConfig.payment["stripe_payments"].apmIcons[this.code];
            },

            getData: function() {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'sender_iban': this.sender_iban(),
                        'sender_name': this.sender_name()
                    }
                };
            },

            validate: function()
            {
                if (this.isRequired() && (!this.sender_iban() || !this.sender_name()))
                {
                    this.messageContainer.addErrorMessage({ "message": "Please complete all required fields." });
                    return false;
                }

                return true;
            },

            placeOrder: function()
            {
                if (this.validate())
                {
                    this._super();
                }
            },

            isOptional: function()
            {
                return (collectBankAccount == 1);
            },

            isRequired: function()
            {
                return (collectBankAccount == 2);
            }
        });
    }
);
