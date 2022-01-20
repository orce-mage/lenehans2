/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'StripeIntegration_Payments/js/view/checkout/trialing_subscriptions',
        'StripeIntegration_Payments/js/action/get-checkout-methods',
        'StripeIntegration_Payments/js/action/get-checkout-session-id',
        'StripeIntegration_Payments/js/action/get-payment-url',
        'StripeIntegration_Payments/js/view/payment/method-renderer/method',
        'stripejs',
        'domReady!'
    ],
    function (
        ko,
        $,
        quote,
        additionalValidators,
        placeOrderAction,
        fullScreenLoader,
        globalMessageList,
        trialingSubscriptions,
        getCheckoutMethods,
        getCheckoutSessionId,
        getPaymentUrlAction,
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/checkout',
                code: "stripe_checkout",
                customRedirect: true,
                shouldPlaceOrder: true,
                checkoutSessionId: null,
                methodIcons: ko.observableArray([])
            },
            redirectAfterPlaceOrder: false,

            initObservable: function()
            {
                this._super().observe(['methodIcons']);

                var params = window.checkoutConfig.payment["stripe_payments"].initParams;

                initStripe(params);

                var self = this;
                var currentTotals = quote.totals();
                var currentBillingAddress = quote.billingAddress();
                var currentShippingAddress = quote.shippingAddress();

                trialingSubscriptions().refresh(quote);
                getCheckoutMethods(quote, self.setPaymentMethods.bind(self));

                quote.billingAddress.subscribe(function(address)
                {
                    if (!address)
                        return;

                    if (self.isAddressSame(address, currentBillingAddress))
                        return;

                    currentBillingAddress = address;

                    getCheckoutMethods(quote, self.setPaymentMethods.bind(self));
                }
                , this);

                quote.shippingAddress.subscribe(function(address)
                {
                    if (!address)
                        return;

                    if (self.isAddressSame(address, currentShippingAddress))
                        return;

                    currentShippingAddress = address;

                    getCheckoutMethods(quote, self.setPaymentMethods.bind(self));
                }
                , this);

                quote.totals.subscribe(function (totals)
                {
                    if (JSON.stringify(totals.total_segments) == JSON.stringify(currentTotals.total_segments))
                        return;

                    currentTotals = totals;

                    trialingSubscriptions().refresh(quote);

                    getCheckoutMethods(quote, self.setPaymentMethods.bind(self));
                }
                , this);

                return this;
            },

            isAddressSame: function(address1, address2)
            {
                var a = this.stringifyAddress(address1);
                var b = this.stringifyAddress(address2);

                return a == b;
            },

            stringifyAddress: function(address)
            {
                if (typeof address == "undefined" || !address)
                    return null;

                return JSON.stringify({
                    "countryId": (typeof address.countryId != "undefined") ? address.countryId : "",
                    "region": (typeof address.region != "undefined") ? address.region : "",
                    "city": (typeof address.city != "undefined") ? address.city : "",
                    "postcode": (typeof address.postcode != "undefined") ? address.postcode : ""
                });
            },

            setPaymentMethods(response)
            {
                var methods = [];
                this.shouldPlaceOrder = true;
                this.checkoutSessionId = null;

                if (typeof response == "string")
                    response = JSON.parse(response);

                if (typeof response.error != "undefined")
                    return this.showError(response.error);

                if (typeof response.methods == "undefined" || response.methods.length == 0)
                    return;
                else
                    methods = response.methods;

                if (typeof response.place_order != "undefined")
                    this.shouldPlaceOrder = response.place_order;

                if (typeof response.checkout_session_id != "undefined")
                    this.checkoutSessionId = response.checkout_session_id;

                var icons = [];
                var self = this;

                if (methods.indexOf('card') >= 0)
                    icons = window.checkoutConfig.payment["stripe_payments"].icons;

                methods.forEach(function(method)
                {
                    if (self.hasPaymentMethod(icons, method))
                        return;

                    if (typeof window.checkoutConfig.payment["stripe_payments"].apmIcons[method] != "undefined")
                    {
                        icons.push({
                            "code": method,
                            "path": window.checkoutConfig.payment["stripe_payments"].apmIcons[method],
                            "name": self.methodName(method)
                        });
                    }
                    else if (method != "card")
                    {
                        icons.push({
                            "code": method,
                            "path": window.checkoutConfig.payment["stripe_payments"].apmIcons["bank"],
                            "name": self.methodName(method)
                        });
                    }
                });

                this.methodIcons(icons);
            },

            hasPaymentMethod: function(collection, code)
            {
                var exists = collection.filter(function (o)
                {
                  return o.hasOwnProperty("code") && o.code == code;
                }).length > 0;

                return exists;
            },

            checkoutPlaceOrder: function()
            {
                var self = this;

                if (additionalValidators.validate())
                {
                    fullScreenLoader.startLoader();
                    getCheckoutSessionId().done(function (response)
                    {
                        if (response && response.length && response.indexOf("cs_") === 0)
                            self.redirect(response);
                        else
                            self.placeOrder();
                    })
                    .error(self.placeOrder.bind(self));
                }

                return false;
            },

            placeOrder: function()
            {
                var self = this;

                placeOrderAction(self.getData(), self.messageContainer)
                .done(function () {
                    getPaymentUrlAction(self.messageContainer).always(function () {
                        fullScreenLoader.stopLoader();
                    }).done(function (response) {
                        fullScreenLoader.startLoader();
                        self.redirect(response);
                    }).error(function () {
                        globalMessageList.addErrorMessage({
                            message: $t('An error occurred on the server. Please try to place the order again.')
                        });
                    });
                }).error(function (e) {
                    globalMessageList.addErrorMessage({
                        message: $t(e.responseJSON.message)
                    });
                });

                return false;
            },

            redirect: function(sessionId)
            {
                try
                {
                    stripe.stripeJs.redirectToCheckout({ sessionId: sessionId }, self.onRedirectFailure);
                }
                catch (e)
                {
                    console.error(e);
                }
            },

            onRedirectFailure: function(result)
            {
                if (result.error)
                    alert(result.error.message);
                else
                    alert("An error has occurred.");
            },

            methodName: function(code)
            {
                if (typeof code == 'undefined')
                    return '';

                switch (code)
                {
                    case 'visa': return "Visa";
                    case 'amex': return "American Express";
                    case 'mastercard': return "MasterCard";
                    case 'discover': return "Discover";
                    case 'diners': return "Diners Club";
                    case 'jcb': return "JCB";
                    case 'unionpay': return "UnionPay";
                    case 'cartes_bancaires': return "Cartes Bancaires";
                    case 'bacs_debit': return "BACS Direct Debit";
                    case 'au_becs_debit': return "BECS Direct Debit";
                    case 'boleto': return "Boleto";
                    case 'acss_debit': return "ACSS Direct Debit / Canadian PADs";
                    case 'ach_debit': return "ACH Direct Debit";
                    case 'oxxo': return "OXXO";
                    case 'klarna': return "Klarna";
                    case 'sepa': return "SEPA Direct Debit";
                    case 'sepa_debit': return "SEPA Direct Debit";
                    case 'sepa_credit': return "SEPA Credit Transfer";
                    case 'sofort': return "SOFORT";
                    case 'ideal': return "iDEAL";
                    case 'paypal': return "PayPal";
                    case 'wechat': return "WeChat Pay";
                    case 'alipay': return "Alipay";
                    case 'grabpay': return "GrabPay";
                    case 'afterpay_clearpay': return "Afterpay / Clearpay";
                    case 'multibanco': return "Multibanco";
                    case 'p24': return "P24";
                    case 'giropay': return "Giropay";
                    case 'eps': return "EPS";
                    case 'bancontact': return "Bancontact";
                    default:
                        return code.charAt(0).toUpperCase() + Array.from(code).splice(1).join('')
                }
            },

            showError: function(message)
            {
                document.getElementById('actions-toolbar').scrollIntoView(true);
                this.messageContainer.addErrorMessage({ "message": message });
            },
        });
    }
);
