/**
 * Checkout/cart gift card logic
 */
define([
    'jquery',
    'underscore',
    'uiComponent',
    'Amasty_GiftCardAccount/js/model/payment/gift-card-messages',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate',
    'Amasty_GiftCardAccount/js/action/loader',
    'Magento_Checkout/js/model/error-processor',
    'Amasty_GiftCardAccount/js/action/gift-code-actions',
    'Magento_Customer/js/model/customer'
], function ($, _, Component, messageContainer, total, getPaymentInformationAction, fullScreenLoader, $t,
    loader, errorProcessor, giftCodeActions, Customer) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_GiftCardAccount/payment/gift-card',
            cardCode: '',
            applyCodes: '',
            loader: {},
            isCart: false,
            emptyFieldText: $t('Enter Gift Card Code'),
            wrongCodeText: $t('Wrong Gift Card Code.'),
            noCodesText: $t('You have no active Gift Card Codes added to your customer account.'),
            guestCodesText: $t('Please login or register as a customer to add ' +
                'Gift Card Codes to your customer account and display here.'),
            links: {
                checkedCards: '${ "amcard-cart-render" }:cards'
            },
            datalistMessage: '',
            isShowDatalist: false,
            options: []
        },

        initialize: function () {
            this._super();
            var codes, availableCodes = [];

            if (total.getSegment('amasty_giftcard')) {
                codes = total.getSegment('amasty_giftcard').title.split(' ').join('');
                this.applyCodes(codes);
            }

            if (!this.applyCodes()) {
                this.applyCodes('');
            }

            if (!_.isUndefined(window.checkoutConfig.amGiftCardAvailableCodes)) {
                _.each(window.checkoutConfig.amGiftCardAvailableCodes, function (code) {
                    availableCodes.push({ value: code });
                })
            }

            this.outsideDatalistClick = this.onOutsideDatalistClick.bind(this);
            this.options(availableCodes);
            this.loader = loader(this.isCart);

            return this;
        },

        initObservable: function () {
            this._super()
                .observe([
                    'cardCode',
                    'checkedCards',
                    'applyCodes',
                    'options',
                    'isShowDatalist',
                    'datalistMessage'
                ]);

            return this;
        },

        setContainer: function (element) {
            this.container = element;
        },

        onDatalistClick: function () {
            var noticeMessage = Customer.isLoggedIn()
                ? this.noCodesText
                : this.guestCodesText;

            if (!this.options().length) {
                this.datalistMessage(noticeMessage);
            }

            this.toggleDatalist();
        },

        toggleDatalist: function () {
            if (!this.isShowDatalist()) {
                this.isShowDatalist(true);
                window.addEventListener('click', this.outsideDatalistClick);
            } else {
                this.hideDatalist();
            }
        },

        onOutsideDatalistClick: function (event) {
            if (!this.container.contains(event.target)) {
                this.hideDatalist();
            }
        },

        onOptionClick: function (value) {
            this.cardCode(value);
            this.hideDatalist();
        },

        hideDatalist: function () {
            this.datalistMessage('');
            this.isShowDatalist(false);
            window.removeEventListener('click', this.outsideDatalistClick);
        },

        /**
         * Gift code remove
         */
        removeSelected: function (cartCode) {
            this.loader.start();

            giftCodeActions.remove(cartCode)
                .done(function (code) {
                    this.removeDone(code);
                }.bind(this))
                .fail(function (response) {
                    total.isLoading(false);
                    this.loader.stop();
                    errorProcessor.process(response, messageContainer);
                }.bind(this));
        },

        removeDone: function (code) {
            var deferred = $.Deferred(),
                appliedCodes = this.applyCodes().split(','),
                message = $t('Gift Card %1 was removed.').replace('%1', code);

            if (appliedCodes.indexOf(code) !== -1) {
                appliedCodes.splice(appliedCodes.indexOf(code), 1);
            }

            total.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                this.applyCodes(appliedCodes.join(','));
                total.isLoading(false);
                this.loader.stop();
            }.bind(this));

            messageContainer.addSuccessMessage({
                'message': message
            });
        },

        /**
         * Gift card code code application procedure
         */
        apply: function () {
            if (!this.validate()) {
                return;
            }

            this.loader.start();

            giftCodeActions.set(this.cardCode()).done(function (newCode) {
                if (newCode) {
                    this.applyDone(newCode);
                }
            }.bind(this))
                .fail(function (response) {
                    this.loader.stop();
                    total.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }.bind(this));
        },

        applyDone: function (newCode) {
            var deferred,
                appliedCodes = this.applyCodes().split(','),
                message = $t('Gift Card "%1" was added.').replace('%1', newCode);

            deferred = $.Deferred();
            total.isLoading(true);
            getPaymentInformationAction(deferred);

            $.when(deferred).done(function () {
                appliedCodes.push(newCode);
                this.applyCodes(appliedCodes.join(','));
                this.loader.stop();
                total.isLoading(false);
                this.cardCode('');
            }.bind(this));

            messageContainer.addSuccessMessage({
                'message': message
            });
        },

        /**
         * Check gift card code
         */
        check: function () {
            if (!this.validate()) {
                return;
            }

            this.loader.start();
            giftCodeActions.check(this.cardCode()).done(function (response) {
                this.loader.stop();

                if (!response.length) {
                    messageContainer.addErrorMessage({
                        'message': this.wrongCodeText
                    });

                    return;
                }

                this.checkedCards(JSON.parse(response));
            }.bind(this));
        },

        validate: function () {
            if (this.cardCode()) {
                return true;
            }

            messageContainer.addErrorMessage({
                'message': $t(this.emptyFieldText)
            });

            return false;
        },

        isGiftCardEnable: function () {
            return window.checkoutConfig.isGiftCardEnabled;
        }
    });
});
