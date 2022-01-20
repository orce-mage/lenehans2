/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'StripeIntegration_Payments/js/action/get-payment-url',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function (
        ko,
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        getPaymentUrlAction,
        additionalValidators,
        quote,
        customerData,
        fullScreenLoader,
        globalMessageList,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/redirect_form',
                customRedirect: false
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

            icons: function()
            {
                if (this.getIcon().length > 0)
                    return [{path: this.getIcon()}];

                return [];
            },

            /** Redirect to Bank */
            placeOrder: function () {
                if (additionalValidators.validate()) {
                    var self = this;

                    placeOrderAction(self.getData(), self.messageContainer)
                    .done(function () {
                        getPaymentUrlAction(self.messageContainer).always(function () {
                            fullScreenLoader.stopLoader();
                        }).done(function (response) {
                            fullScreenLoader.startLoader();
                            customerData.invalidate(['cart']);
                            if (self.customRedirect)
                                self.redirect(response);
                            else
                                $.mage.redirect(response);
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
                }
            }
        });
    }
);
