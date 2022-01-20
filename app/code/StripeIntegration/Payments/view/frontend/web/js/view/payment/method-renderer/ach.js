/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'StripeIntegration_Payments/js/view/payment/method-renderer/method',
        'Magento_Checkout/js/model/quote',
        'mage/translate'
    ],
    function (
        ko,
        $,
        Component,
        quote,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/ach',
                code: "ach"
            },
            redirectAfterPlaceOrder: true,

            initObservable: function ()
            {
                this._super();
                this.observe([
                    'accountHolderName',
                    'accountHolderType',
                    'accountNumber',
                    'routingNumber',
                    'token'
                ]);
                this.accountHolderTypes = ko.observableArray(["Individual", "Company"]);

                var params = window.checkoutConfig.payment["stripe_payments"].initParams;

                initStripe(params);

                return this;
            },

            getData: function ()
            {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'token': this.token()
                    }
                };
            },

            getParams: function()
            {
                return {
                    country: quote.billingAddress().countryId,
                    currency: quote.totals().quote_currency_code,
                    routing_number: this.routingNumber(),
                    account_number: this.accountNumber(),
                    account_holder_name: this.accountHolderName(),
                    account_holder_type: this.accountHolderType()
                };
            },

            generateToken: function(onSuccess, onError)
            {
                var self = this;
                stripe.stripeJs.createToken('bank_account', this.getParams()).then(function(result)
                {
                    if (result.token)
                        onSuccess(result.token.id, result.token.bank_account);
                    else
                    {
                        if (result.error)
                            onError(result.error.message);
                        else
                            onError('Your bank account details could not be used to verify your account');
                    }
                });
            },

            validate: function()
            {
                this.messageContainer.clear();

                if (!this.accountHolderName())
                {
                    this.showError("Please specify an account holder name");
                    return false;
                }

                if (!this.accountHolderType())
                {
                    this.showError("Please specify an account type");
                    return false;
                }

                if (!this.accountNumber())
                {
                    this.showError("Please specify an account number");
                    return false;
                }

                if (!this.routingNumber())
                {
                    this.showError("Please specify a routing number");
                    return false;
                }

                // check that a token has been generated
                return true;
            },

            placeOrder: function ()
            {
                var self = this;

                if (!this.validate())
                    return;

                this.isPlaceOrderActionAllowed(false);
                var submitOrder = this._super.bind(this);

                this.generateToken(
                    function(tokenId, bankAccount)
                    {
                        self.token(tokenId);
                        submitOrder();
                    },
                    function(errorMessage)
                    {
                        self.showError(errorMessage);
                        self.isPlaceOrderActionAllowed(true);
                    });
            },

            showError: function(message)
            {
                // document.getElementById('ach-actions-toolbar').scrollIntoView(true);
                this.messageContainer.addErrorMessage({ "message": $t(message) });
            }
        });
    }
);
