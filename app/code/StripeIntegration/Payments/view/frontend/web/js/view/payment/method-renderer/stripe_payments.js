define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'StripeIntegration_Payments/js/action/get-payment-url',
        'StripeIntegration_Payments/js/action/get-installment-plans',
        'StripeIntegration_Payments/js/view/checkout/trialing_subscriptions',
        'stripe_payments_express',
        'mage/translate',
        'mage/url',
        'jquery',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/storage',
        'mage/url',
        'Magento_CheckoutAgreements/js/model/agreement-validator'
    ],
    function (
        ko,
        Component,
        globalMessageList,
        quote,
        customer,
        getPaymentUrlAction,
        getInstallmentPlans,
        trialingSubscriptions,
        stripeExpress,
        $t,
        url,
        $,
        placeOrderAction,
        additionalValidators,
        redirectOnSuccessAction,
        storage,
        urlBuilder,
        agreementValidator
    ) {
        'use strict';

        return Component.extend({
            externalRedirectUrl: null,
            defaults: {
                template: 'StripeIntegration_Payments/payment/form',
                stripePaymentsCardSave: false,
                stripePaymentsShowApplePaySection: false
            },

            initObservable: function ()
            {
                this._super()
                    .observe([
                        'stripePaymentsError',
                        'stripePaymentsCardName',
                        'stripePaymentsCardNumber',
                        'stripePaymentsCardExpMonth',
                        'stripePaymentsCardExpYear',
                        'stripePaymentsCardVerificationNumber',
                        'stripePaymentsStripeJsToken',
                        'stripePaymentsCardSave',
                        'stripePaymentsSelectedCard',
                        'stripePaymentsShowNewCardSection',
                        'stripePaymentsShowApplePaySection',
                        'stripeCreatingToken',
                        'fetchingInstallments',
                        'isPaymentRequestAPISupported',
                        'installmentPlans',
                        'selectedInstallmentPlan'
                    ]);

                this.stripePaymentsSelectedCard.subscribe(this.onSelectedCardChanged, this);
                this.stripePaymentsSelectedCard('new_card');
                if (!this.hasSavedCards())
                    this.stripePaymentsShowNewCardSection(true);
                else
                {
                    for (var i = 0; i < this.config().savedCards.length; i++)
                        this.config().savedCards[i].cardType = this.cardType(this.config().savedCards[i].brand);
                }

                this.showSavedCardsSection = ko.computed(function()
                {
                    return this.hasSavedCards() && this.isBillingAddressSet();
                }, this);

                this.displayAtThisLocation = ko.computed(function()
                {
                    return this.config().applePayLocation == 1;
                }, this);

                this.showNewCardSection = ko.computed(function()
                {
                    return this.stripePaymentsShowNewCardSection() &&
                        this.isBillingAddressSet();
                }, this);

                this.showSaveCardOption = ko.computed(function()
                {
                    return this.config().showSaveCardOption && customer.isLoggedIn() && this.showNewCardSection();
                }, this);

                this.hasIcons = ko.pureComputed(function()
                {
                    return (this.config().icons.length > 0);
                }, this);

                this.iconsRight = ko.pureComputed(function() {
                    if (this.config().iconsLocation == "right")
                        return true;
                    return false;
                }, this);

                var self = this;

                stripeExpress.onPaymentSupportedCallbacks.push(function()
                {
                    self.isPaymentRequestAPISupported(true);
                    self.stripePaymentsShowApplePaySection(true);
                });

                trialingSubscriptions().refresh(quote);

                var currentTotals = quote.totals();

                quote.billingAddress.subscribe(function(address)
                {
                    if (!address)
                        return;

                    setTimeout(stripe.refreshSetupIntent, 0, false);
                });

                quote.totals.subscribe(function (totals)
                {
                    if (JSON.stringify(totals.total_segments) == JSON.stringify(currentTotals.total_segments))
                        return;

                    currentTotals = totals;

                    // Wait for Magento to commit the changes before re-initializing the PRAPI
                    setTimeout(this.initPRAPI.bind(this), 500);

                    trialingSubscriptions().refresh(quote);
                }
                , this);

                if (this.config().isSaveCardCheckboxChecked || this.config().alwaysSaveCard)
                    this.stripePaymentsCardSave(true);

                return this;
            },

            hasSavedCards: function()
            {
                return (typeof this.config().savedCards != 'undefined'
                    && this.config().savedCards != null
                    && this.config().savedCards.length);
            },

            onSelectedCardChanged: function(newValue)
            {
                if (newValue == 'new_card')
                    this.stripePaymentsShowNewCardSection(true);
                else
                    this.stripePaymentsShowNewCardSection(false);
            },

            onCheckoutFormRendered: function()
            {
                var self = this;
                var params = window.checkoutConfig.payment["stripe_payments"].initParams;
                initStripe(params, function(err)
                {
                    if (err)
                    {
                        self.stripePaymentsError(err);
                        return self.showError(self.maskError(err));
                    }
                    else
                        self.stripePaymentsError(null);

                    stripe.initStripeElements(params.locale);
                    stripe.onWindowLoaded(stripe.initStripeElements.bind(stripe, params.locale));
                    stripe.refreshSetupIntent(false);
                });
            },

            initPRAPI: function()
            {
                if (!this.config().isApplePayEnabled)
                    return;

                if (this.config().applePayLocation != 1)
                    return;

                var self = this;

                var params = self.config().initParams;
                stripeExpress.initStripeExpress('#payment-request-button', params, 'checkout', self.config().prapiButtonConfig,
                    function (paymentRequestButton, paymentRequest, params, prButton) {
                        stripeExpress.initCheckoutWidget(paymentRequestButton, paymentRequest, prButton, self.validatePRAPI.bind(self));
                    }
                );
            },

            validatePRAPI: function(ev)
            {
                if (!additionalValidators.validate() || !agreementValidator.validate())
                {
                    ev.preventDefault();

                    if (!agreementValidator.validate())
                        var message = $t("Please agree to the terms and conditions before placing the order.");
                    else
                        var message = $t("Please complete all required fields before placing the order.");

                    if (this.config().applePayLocation == 2)
                        this.showGlobalError(message)
                    else
                        this.showError(message);
                }
            },

            isBillingAddressSet: function()
            {
                return quote.billingAddress() && quote.billingAddress().canUseForBilling();
            },

            isPlaceOrderEnabled: function()
            {
                if (this.stripePaymentsError())
                    return false;

                if (this.stripeCreatingToken())
                    return false;

                if (this.fetchingInstallments())
                    return false;

                if (this.installmentPlans())
                    return false;

                if (this.isBillingAddressSet())
                    stripe.quote = quote;

                return this.isBillingAddressSet();
            },

            isZeroDecimal: function(currency)
            {
                var currencies = ['bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
                    'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'];

                return currencies.indexOf(currency) >= 0;
            },

            icons: function()
            {
                return this.config().icons;
            },

            showApplePaySection: function()
            {
                return (this.stripePaymentsShowApplePaySection || this.isPaymentRequestAPISupported);
            },

            config: function()
            {
                return window.checkoutConfig.payment[this.getCode()];
            },

            isActive: function(parents)
            {
                return true;
            },

            isNewCard: function()
            {
                if (!this.hasSavedCards()) return true;
                if (this.stripePaymentsSelectedCard() == 'new_card') return true;
                return false;
            },

            maskError: function(err)
            {
                return stripe.maskError(err);
            },

            placeOrder: function()
            {
                if (!additionalValidators.validate())
                    return;

                stripe.applePaySuccess = false;

                var self = this;

                this.stripePaymentsStripeJsToken(null);
                this.stripeCreatingToken(true);
                stripe.quote = quote;
                stripe.customer = customer;

                // Create a new source
                if (this.stripePaymentsSelectedCard() == 'new_card')
                    stripe.sourceId = null;
                // Use one of the selected saved cards
                else
                    stripe.sourceId = stripe.cleanToken(this.stripePaymentsSelectedCard());

                createStripeToken(function(err, token, response)
                {
                    self.stripeCreatingToken(false);
                    if (err)
                    {
                        self.showError(self.maskError(err));
                        return;
                    }
                    else if (self.shouldDisplayInstallmentPlans.bind(self)())
                    {
                        self.stripePaymentsStripeJsToken(token);
                        self.fetchInstallmentPlans.bind(self)(token);
                    }
                    else
                    {
                        self.stripePaymentsStripeJsToken(token);
                        self.placeOrderWithToken();
                    }
                });
            },

            shouldDisplayInstallmentPlans: function()
            {
                // Only card issuers from Mexico have installment plans available
                var countryId = quote.billingAddress().countryId;
                if (countryId.toLowerCase() != "mx")
                    return false;

                return this.config().isInstallmentPlansEnabled;
            },

            fetchInstallmentPlans: function(token)
            {
                var self = this;

                self.fetchingInstallments(true);
                getInstallmentPlans(token)
                    .always(function()
                    {
                        self.fetchingInstallments(false);
                    })
                    .done(function (plans)
                    {
                        try {
                            plans = JSON.parse(plans);

                            for (var i = 0; i < plans.length; i++)
                            {
                                plans[i].value = i;
                                plans[i].label = plans[i].count + ' ' + plans[i].interval;
                                if (plans[i].count > 1)
                                    plans[i].label += 's';
                            }
                        } catch (e) {
                            plans = [];
                        }

                        if (plans.length > 0)
                        {
                            $(".payment-method-content.stripe-payments-installments-form").slideUp(0);
                            self.installmentPlans(plans);
                            self.expandInstallments();
                        }
                        else
                        {
                            self.stripeCreatingToken(false);
                            self.placeOrderWithToken();
                        }
                    })
                    .error(function(err)
                    {
                        self.stripeCreatingToken(false);
                        console.warn(err);

                        // If for any reason we can't fetch the installment plans, just place the order
                        self.placeOrderWithToken();
                    });
            },

            /**
             * Place order.
             */
            placeOrderWithToken: function (data, event)
            {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                var customErrorHandler = this.handlePlaceOrderErrors.bind(this);

                if (!this.stripePaymentsStripeJsToken())
                {
                    this.showError('Could not process card details, please try again.');
                    return false;
                }

                if (this.validate())
                {
                    this.isPlaceOrderActionAllowed(false);

                    this.getPlaceOrderDeferredObject()
                        .fail(customErrorHandler)
                        .done(
                            function () {
                                self.afterPlaceOrder();

                                if (self.redirectAfterPlaceOrder) {
                                    redirectOnSuccessAction.execute();
                                }
                            }
                        );

                    return true;
                }

                return false;
            },

            /**
             * @return {*}
             */
            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
            },

            handlePlaceOrderErrors: function (result)
            {
                var self = this;
                var status = result.status + " " + result.statusText;

                if (stripe.isAuthenticationRequired(result.responseJSON.message))
                {
                    return stripe.processNextAuthentication(function(err)
                    {
                        if (err)
                        {
                            self.showError(err);
                            return;
                        }

                        self.placeOrderWithToken();
                    });
                }
                else if (this.installmentPlans())
                    this.collapseInstallments();
            },

            showGlobalError: function(message)
            {
                document.getElementById('checkout').scrollIntoView(true);
                globalMessageList.addErrorMessage({ "message": message });
            },

            showError: function(message)
            {
                document.getElementById('actions-toolbar').scrollIntoView(true);
                this.messageContainer.addErrorMessage({ "message": message });
            },

            // afterPlaceOrder: function()
            // {
            //     if (this.redirectAfterPlaceOrder)
            //         return;
            // },

            validate: function(elm)
            {
                if (!this.isNewCard() && !this.stripePaymentsSelectedCard())
                    return this.showError('Please select a card!');

                return additionalValidators.validate();
            },

            getCode: function()
            {
                return 'stripe_payments';
            },

            getData: function()
            {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_stripejs_token': this.stripePaymentsStripeJsToken(),
                        'cc_saved': this.stripePaymentsSelectedCard(),
                        'cc_save': this.stripePaymentsCardSave()
                    }
                };

                if (this.installmentPlans() && document.getElementById('stripe_installment_over_time').checked)
                    data.additional_data.selected_plan = this.selectedInstallmentPlan();

                return data;
            },

            getCcMonthsValues: function() {
                return $.map(this.getCcMonths(), function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    };
                });
            },

            getCcYearsValues: function() {
                return $.map(this.getCcYears(), function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },

            prapiTitle: function()
            {
                return this.config().prapiTitle;
            },

            getCcMonths: function()
            {
                return window.checkoutConfig.payment[this.getCode()].months;
            },

            getCcYears: function()
            {
                return window.checkoutConfig.payment[this.getCode()].years;
            },

            getCvvImageUrl: function() {
                return window.checkoutConfig.payment[this.getCode()].cvvImageUrl;
            },

            getCvvImageHtml: function() {
                return '<img src="' + this.getCvvImageUrl() +
                    '" alt="' + 'Card Verification Number Visual Reference' +
                    '" title="' + 'Card Verification Number Visual Reference' +
                    '" />';
            },
            cardType: function(code)
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
                    default:
                        return code.charAt(0).toUpperCase() + Array.from(code).splice(1).join('')
                }
            },

            expandInstallments: function()
            {
                $(".payment-method-content.stripe-payments-card-form").slideUp(500);
                $(".payment-method-content.stripe-payments-installments-form").slideDown(500);
            },

            collapseInstallments: function()
            {
                this.installmentPlans(null);
                $(".payment-method-content.stripe-payments-card-form").slideDown(500);
                $(".payment-method-content.stripe-payments-installments-form").slideUp(500);
            },

            focusInstallments: function()
            {
                document.getElementById('stripe_installment_over_time').checked = true;
            }
        });
    }
);
