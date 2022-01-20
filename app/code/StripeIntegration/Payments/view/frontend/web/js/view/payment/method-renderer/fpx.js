/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'StripeIntegration_Payments/js/view/payment/method-renderer/method',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'StripeIntegration_Payments/js/action/get-payment-url',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function (
        $,
        ko,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        getPaymentUrlAction,
        additionalValidators,
        customerData,
        fullScreenLoader,
        globalMessageList,
        $t
    ) {
        'use strict';

        var banks = window.checkoutConfig.payment.stripe_payments_fpx.banks;

        return Component.extend({
            defaults: {
                self: this,
                template: 'StripeIntegration_Payments/payment/fpx',
                selectedBank: null,
                selectedBankName: null,
                isDropdownOpen: false,
                code: "fpx"
            },
            redirectAfterPlaceOrder: false,

            initObservable: function ()
            {
                this._super()
                    .observe([
                        'selectedBank',
                        'selectedBankName',
                        'isDropdownOpen'
                    ]);

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

            getBanks: function()
            {
                return banks;
            },

            getSelectedBank: function()
            {
                if (this.selectedBank())
                    return this.selectedBank().value;

                return null;
            },

            getSelectedBankName: function()
            {
                if (this.selectedBank())
                    return this.selectedBank().label;

                return null;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'bank': this.getSelectedBank(),
                    }
                };
            },

            getBankIcon: function(data)
            {
                return data.value;
            },

            showError: function(message)
            {
                this.messageContainer.addErrorMessage({ "message": message });
            },

            selectBank: function(data)
            {
                $root.selectedBank(data.value);
                $root.isDropdownOpen(false);
            },

            toggleDropdown: function(self)
            {
                self.isDropdownOpen(!self.isDropdownOpen());
            },

            dummyArray: function()
            {
                // Some kind of ko bug prevents the 'click' binding from being binded, this solves it
                return [this];
            },

            /** Redirect to Bank */
            placeOrder: function ()
            {
                if (additionalValidators.validate())
                {
                    if (!this.getSelectedBank())
                        return this.showError($t('Please select your bank before placing the order'));

                    var self = this;
                    selectPaymentMethodAction(this.getData());

                    placeOrderAction(self.getData(), self.messageContainer).done(function ()
                    {
                        getPaymentUrlAction(self.messageContainer).always(function ()
                        {
                            fullScreenLoader.stopLoader();
                        })
                        .done(function (response)
                        {
                            fullScreenLoader.startLoader();
                            customerData.invalidate(['cart']);
                            $.mage.redirect(response);
                        })
                        .error(function ()
                        {
                            globalMessageList.addErrorMessage({
                                message: $t('An error occurred on the server. Please try to place the order again.')
                            });
                        });
                    });

                    return false;
                }
            }
        });
    }
);
