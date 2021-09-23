/**
 * Widget gift card checker logic
 */
define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'Amasty_GiftCardAccount/js/action/loader',
    'Amasty_GiftCardAccount/js/view/widget/messages/message-model',
], function ($, Component, $t, loader, messageContainer) {
    'use strict';

    return Component.extend({
        defaults: {
            cardCode: '',
            checkCardUrl: '',
            loader: {},
            emptyFieldText: $t('Enter Gift Card Code'),
            wrongCodeText: $t('Wrong Gift Card Code.'),
            activeCodeText: $t('Gift Card Code is available'),
            links: {
                checkedCards: '${ "amcard-cart-checker-render" }:cards'
            }
        },

        initialize: function () {
            this._super();
            this.loader = loader(true);

            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['cardCode', 'checkedCards']);

            return this;
        },

        /**
         * Check gift card code
         */
        check: function () {
            if (!this.validate()) {
                return;
            }

            this.loader.start();
            $.ajax({
                url: this.checkCardUrl,
                data: {amgiftcard: this.cardCode},
                type: 'post'
            }).done(function (response) {
                this.loader.stop();

                if (!response.length) {
                    messageContainer.addErrorMessage({'message': this.wrongCodeText});

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
                'message': this.emptyFieldText
            });

            return false;
        },
    });
});
