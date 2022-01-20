/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/checkout-data',
    'uiComponent'
], function (ko, $, selectPaymentMethodAction, checkoutData, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'StripeIntegration_Payments/payment/klarna/payment_option'
        },
        isKlarnaWidgetLoading: ko.observable(true),
        isRadioButtonVisible: ko.observable(true),
        availableOptions: ko.observableArray(),

        initialize: function () {
            this._super()
                .initObservable();

            return this;
        },

        initObservable: function () {
            this._super();

            var self = this;
            this.isPlaceOrderDisabled = ko.computed(function()
            {
                return self.parentComponent.isPlaceOrderDisabled();
            });

            this.isActive = ko.computed(function()
            {
                return (self.parentComponent.selectedPaymentOption() == self.paymentOptionCode) &&
                    (self.parentComponent.isChecked() == self.parentComponent.getCode());
            });

            this.isVisible = ko.computed(function()
            {
                var options = self.availableOptions();
                if (options.indexOf(self.paymentOptionCode) >= 0)
                    return true;

                return false;
            });

            var options = self.availableOptions();
            options.push(self.paymentOptionCode);
            self.availableOptions(options);

            return this;
        },

        getPaymentOptionCode: function()
        {
            return this.paymentOptionCode;
        },

        placeOrder: function()
        {
            this.parentComponent.placeOrder();
        },

        initKlarnaWidget: function()
        {
            var self = this;

            var containers = $('.klarna-payment-option-container', '#' + this.paymentOptionCode);

            for (var i = 0; i < containers.length; i++)
            {
                var category = containers[i].dataset.klarnaCategory;
                var containerId = containers[i].id;

                try
                {
                    this.isKlarnaWidgetLoading = true;
                    Klarna.Payments.load({
                        container: "#" + containerId,
                        payment_method_category: category,
                        instance_id : "klarna-payments-instance-" + category
                    },
                    function(res)
                    {
                        if (res.show_form)
                        {
                            // This payment method category is available
                        }
                        else
                        {
                            // This payment method category is not available
                            var options = self.availableOptions();
                            var index = options.indexOf(self.paymentOptionCode);
                            if (index >= 0)
                            {
                                options.splice(index, 1);
                                self.availableOptions(options);
                            }
                        }
                    });
                }
                catch (e)
                {
                    console.warn(e.message);
                }
            }
        },

        selectPaymentMethod: function ()
        {
            selectPaymentMethodAction(this.parentComponent.getData());
            checkoutData.setSelectedPaymentMethod(this.parentComponent.item.method);
            this.parentComponent.selectedPaymentOption(this.paymentOptionCode);
            this.parentComponent.selectedPaymentOptionCategory(this.key);
            return true;
        },

        hasMultiplePaymentOptions: function()
        {
            return this.amountOfPaymentOptions > 0;
        },

        isPlaceOrderDisabled: function()
        {
            return this.parentComponent.isPlaceOrderDisabled() || this.isKlarnaWidgetLoading();
        }
    });
});
