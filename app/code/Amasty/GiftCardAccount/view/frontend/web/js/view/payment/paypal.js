/**
 * Paypal gift card logic
 */
define([
    'jquery',
    'uiComponent',
    'Amasty_GiftCardAccount/js/model/payment/gift-card-messages',
    'mage/translate',
    'Amasty_GiftCardAccount/js/action/loader'
], function ($, Component, messageContainer, $t, loader) {
    'use strict';

    return Component.extend({
        defaults: {
            cardCode: '',
            loader: {},
            codes: [],
            cards: [],
            isGiftCardEnable: false,
            formSelector: '[data-amcard-js="paypal-form"]'
        },

        initialize: function () {
            this._super();

            this.loader = loader(true);

            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['cardCode', 'isGiftCardEnable', 'codes', 'cards']);

            return this;
        },

        /**
         * Gift card code application procedure
         */
        apply: function (event) {
            var form = $(this.formSelector);

            event.preventDefault();

            if (this.validate(form)) {
                this.loader.start();
                form.submit();
            }
        },

        /**
         * Check using gift card
         */
        check: function () {
            var message = $t('Wrong Gift Card Code.'),
                form = $(this.formSelector);

            if (!this.validate(form)) {
                return;
            }

            this.loader.start();

            $.ajax({
                url: this.checkCardUrl,
                data: { 'amgiftcard': this.cardCode() },
                type: 'post',
                showLoader: true,
                success: function (response) {
                    this.loader.stop();

                    if (!response.length) {
                        messageContainer.addErrorMessage({
                            'message': message
                        });

                        return;
                    }

                    this.cards(JSON.parse(response));
                }.bind(this)
            });
        },

        validate: function (form) {
            return form.validation() && form.validation('isValid');
        }
    });
});
