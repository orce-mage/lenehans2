/**
 * Account gift cards
 */
define([
    'uiComponent',
    'jquery',
    'mage/translate',
    'Amasty_GiftCardAccount/js/action/loader',
    'Amasty_GiftCardAccount/js/action/customer-message',
    'Amasty_GiftCardAccount/js/action/account-gift-code-actions'
], function (Component, $, $t, loader, customerMessage, giftCodeActions) {
    'use strict';

    return Component.extend({
        defaults: {
            addCardUrl: '',
            cardCode: '',
            isAccount: 0,
            isVisibleMessage: false,
            errorMessage: '',
            cards: [],
            emptyFieldText: $t('Enter Gift Card Code')
        },

        initObservable: function () {
            this._super()
                .observe(['cards', 'cardCode', 'isVisibleMessage', 'errorMessage']);

            this.cards();
            this.loader = loader(this.isCart);
            this.customerMessage = customerMessage(this.isVisibleMessage, this.errorMessage);

            return this;
        },

        addGiftCode: function () {
            if (!this.cardCode()) {
                this.customerMessage.setVisibleMessage(this.emptyFieldText);

                return;
            }

            giftCodeActions.add(this.addCardUrl, this.cardCode()).done(function (response) {
                if (response.error) {
                    this.customerMessage.setVisibleMessage(response.message);
                } else {
                    this.cards(response);
                }

                this.cardCode('');
            }.bind(this));
        }
    });
});
