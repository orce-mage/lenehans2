/**
 * Account gift codes
 */
define([
    'uiElement',
    'jquery',
    'Amasty_GiftCardAccount/js/action/customer-message',
    'Amasty_GiftCardAccount/js/action/account-gift-code-actions'
], function (Element, $, customerMessage, giftCodeActions) {
    'use strict';

    return Element.extend({
        defaults: {
            addCardUrl: '',
            isAccount: '',
            links: {
                cards: '${ "amcard-giftcards" }:cards',
                isVisibleMessage: '${ "amcard-giftcards" }:isVisibleMessage',
                errorMessage: '${ "amcard-giftcards" }:errorMessage'
            }
        },

        initObservable: function () {
            this._super()
                .observe(['cards', 'isVisibleMessage', 'errorMessage']);

            this.customerMessage = customerMessage(this.isVisibleMessage, this.errorMessage);

            return this;
        },

        removeGiftCode: function (e, url) {
            var codeElement = $(e.currentTarget).parent();

            giftCodeActions.remove(url).done(function (response) {
                if (!response.error) {
                    codeElement.remove();

                    return;
                }

                this.customerMessage.setVisibleMessage(response.message);
            }.bind(this));
        }
    });
});
