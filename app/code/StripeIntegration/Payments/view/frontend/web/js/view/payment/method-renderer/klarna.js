/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'uiLayout',
        'uiRegistry',
        'StripeIntegration_Payments/js/view/payment/method-renderer/method',
        'StripeIntegration_Payments/js/action/get-klarna-payment-options',
        'mage/translate',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'klarnapi'
    ],
    function (
        ko,
        $,
        layout,
        registry,
        Component,
        getKlarnaPaymentOptions,
        $t,
        additionalValidators,
        quote,
        customer
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/klarna',
                code: "klarna"
            },
            redirectAfterPlaceOrder: false,
            isInitialLoad: ko.observable(true),
            isLoading: ko.observable(false),
            errorMessage: ko.observable(null),
            paymentOptions: ko.observableArray(null),
            uiPaymentOptions: ko.observableArray(null),
            sourceId: ko.observable(null),
            rawPaymentOptions: null,

            initObservable: function()
            {
                this._super();

                this.observe(['selectedPaymentOption', 'selectedPaymentOptionCategory']);

                var self = this;
                this.isPlaceOrderDisabled = ko.computed(function()
                {
                    var allowed = self.isPlaceOrderActionAllowed();
                    var isPaymentOptionSelected = self.selectedPaymentOption();
                    return (!allowed || self.errorMessage() || !isPaymentOptionSelected);
                });

                this.showPaymentOptions = ko.computed(function()
                {
                    return !self.isLoading() && !self.errorMessage();
                });

                this.showKlarnaSection = ko.computed(function()
                {
                    return !self.isInitialLoad() && !self.paymentOptions();
                });

                getKlarnaPaymentOptions(quote.billingAddress(), quote.guestEmail, this.sourceId())
                    .done(this.onKlarnaPaymentOptions.bind(this))
                    .fail(this.onKlarnaPaymentOptionsFailed.bind(this));

                return this;
            },

            getPaymentOptions: function()
            {
                return this.uiPaymentOptions()
            },

            showPaymentOption: function(key)
            {
                if (this.selectedPaymentOption() == key)
                    return true;

                return false;
            },

            onKlarnaPaymentOptions: function(data)
            {
                this.isInitialLoad(false);
                this.resetPaymentForm();

                if (typeof data == "string")
                {
                    try
                    {
                        data = JSON.parse(data);
                    }
                    catch (e)
                    {
                        this.errorMessage($t('The Klarna payment options could not be loaded.'));
                    }
                }

                Klarna.Payments.init({
                  client_token: data.clientToken
                });

                this.rawPaymentOptions = this.convertPaymentOptionsToArray(data.paymentOptions);
                this.paymentOptions(this.rawPaymentOptions); // Will trigger the template rendering

                if (!this.paymentOptions() || this.paymentOptions().length == 0)
                {
                    this.errorMessage($t('Sorry, there are no available payment options.'));
                    this.sourceId(null);
                }
                else
                {
                    this.sourceId(data.sourceId);
                    this.selectedPaymentOption(this.paymentOptions()[0].key);
                    this.createUiComponents(this.rawPaymentOptions);
                }
            },

            createUiComponents: function(paymentOptions)
            {
                var components = [];

                for (var i = 0; i < paymentOptions.length; i++)
                {
                    var option = {
                        name: paymentOptions[i].key,
                        parent: this.name,
                        component: 'StripeIntegration_Payments/js/view/payment/method-renderer/klarna/payment_option',
                        config: {
                            title: paymentOptions[i].name,
                            paymentOptionCode: this.getCode() + '_' + paymentOptions[i].key,
                            key: paymentOptions[i].key,
                            elemType: 'klarna_payment_option',
                            item: this.item,
                            parentComponent: this,
                            amountOfPaymentOptions: paymentOptions.length,
                            paymentData: {
                                'method': this.item.method,
                                'additional_data': {
                                    'source_id': this.sourceId()
                                }
                            }
                        },
                    };
                    layout([option], this, false, false);
                    var component = registry.get(paymentOptions[i].key);
                    components.push(component);
                }
            },

            onKlarnaPaymentOptionsFailed: function(response)
            {
                this.resetPaymentForm();
                this.isInitialLoad(false);
                this.errorMessage($t(response.responseJSON.message));
            },

            resetPaymentForm: function()
            {
                this.isLoading(false);
                this.paymentOptions(null);
                this.rawPaymentOptions = null;
                this.sourceId(null);
                this.errorMessage(null);
            },

            convertPaymentOptionsToArray: function(options)
            {
                var ret = [];

                for (var key in options)
                {
                    if (options.hasOwnProperty(key))
                    {
                        ret.push(options[key]);
                    }
                }

                return ret;
            },

            getData: function()
            {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'source_id': this.sourceId()
                    }
                };

                return data;
            },

            isPaymentOption: function(elem)
            {
                if (typeof elem.elemType != "undefined" && elem.elemType == "klarna_payment_option")
                    return true;

                return false;
            },

            placeOrder: function()
            {
                if (!this.validate() || !additionalValidators.validate())
                    return false;

                this.isPlaceOrderActionAllowed(false);
                var self = this;
                var parentPlaceOrder = this._super.bind(this);

                try
                {
                    Klarna.Payments.authorize({
                        instance_id : "klarna-payments-instance-" + this.selectedPaymentOptionCategory(),
                        payment_method_category : this.selectedPaymentOptionCategory()
                    },
                    function(res)
                    {
                        if (res.approved)
                        {
                            parentPlaceOrder();
                            // hide form in case of server side exception?
                        }
                        else
                        {
                            if (res.error)
                            {
                                // Payment not authorized or an error has occurred
                                console.debug(res);
                                alert("Sorry, an error has occurred");
                                // recreate source?
                            }
                            else
                            {
                                // Klarna displays the error in this case
                                self.isPlaceOrderActionAllowed(true);
                            }
                        }
                    });
                }
                catch (e)
                {
                    this.errorMessage(e.message);
                }
            }

        });
    }
);
